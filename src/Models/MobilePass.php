<?php

namespace Spatie\LaravelMobilePass\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Response;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\ApplePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\CannotDownload;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Support\Apple\DownloadableMobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;

/**
 * @property string $builder_name
 * @property Platform $platform
 * @property array $images
 * @property array $content
 * @property string|null $download_name
 * @property Carbon $updated_at
 * @property Carbon|null $expired_at
 * @property Collection<int, AppleMobilePassRegistration> $registrations
 * @property Collection<int, GoogleMobilePassEvent> $googleEvents
 */
class MobilePass extends Model implements Attachable, Responsable
{
    use HasFactory;
    use HasUuids;

    public $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::updated(function (MobilePass $mobilePass) {
            [$configKey, $default] = match ($mobilePass->platform) {
                Platform::Apple => ['notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class],
                Platform::Google => ['notify_google_of_pass_update', NotifyGoogleOfPassUpdateAction::class],
            };

            /** @var class-string $action */
            $action = Config::getActionClass($configKey, $default);

            PushPassUpdateJob::dispatch($mobilePass, $action);
        });
    }

    /** @return HasMany<AppleMobilePassRegistration, $this> */
    public function registrations(): HasMany
    {
        $modelClass = Config::appleMobilePassRegistrationModel();

        return $this->hasMany($modelClass, 'pass_serial');
    }

    public function devices(): HasManyThrough
    {
        $modelClass = Config::appleMobilePassRegistrationModel();
        $deviceModelClass = Config::appleDeviceModel();

        return $this->hasManyThrough($deviceModelClass, $modelClass, 'pass_serial', 'id', 'id', 'device_id');
    }

    /** @return HasMany<GoogleMobilePassEvent, $this> */
    public function googleEvents(): HasMany
    {
        $modelClass = Config::googleMobilePassEventModel();

        return $this->hasMany($modelClass, 'mobile_pass_id');
    }

    public function isCurrentlySavedToGoogleWallet(): bool
    {
        $latest = $this->googleEvents()->orderByDesc('received_at')->first();

        return $latest !== null && $latest->event_type === 'save';
    }

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'content' => 'json',
            'images' => 'json',
            'expired_at' => 'datetime',
        ];
    }

    public function expire(): self
    {
        match ($this->platform) {
            Platform::Apple => $this->expireAsApple(),
            Platform::Google => $this->expireAsGoogle(),
        };

        return $this;
    }

    protected function expireAsApple(): void
    {
        $content = $this->content;
        $content['voided'] = true;
        $content['expirationDate'] = now()->toIso8601String();

        $this->update([
            'content' => $content,
            'expired_at' => now(),
        ]);
    }

    protected function expireAsGoogle(): void
    {
        $content = $this->content;
        $content['googleObjectPayload']['state'] = 'EXPIRED';

        $this->update([
            'content' => $content,
            'expired_at' => now(),
        ]);
    }

    public function addToWalletUrl(): string
    {
        return match ($this->platform) {
            Platform::Apple => $this->addToAppleWalletUrl(),
            Platform::Google => $this->addToGoogleWalletUrl(),
        };
    }

    protected function addToAppleWalletUrl(): string
    {
        return URL::signedRoute('mobile-pass.apple.download', ['mobilePass' => $this->id]);
    }

    protected function addToGoogleWalletUrl(): string
    {
        $objectResource = str_replace('Class', 'Object', $this->content['googleClassType']);
        $resourceKey = $objectResource.'s';

        $jwt = app(GoogleJwtSigner::class)->signSaveUrlJwt([
            $resourceKey => [['id' => $this->content['googleObjectId']]],
        ]);

        return 'https://pay.google.com/gp/v/save/'.$jwt;
    }

    public function airlinePassBuilder(): AirlinePassBuilder
    {
        $this->assertBuilderName(AirlinePassBuilder::name());

        /** @var AirlinePassBuilder */
        return $this->builder();
    }

    public function boardingPassBuilder(): BoardingPassBuilder
    {
        $this->assertBuilderName(BoardingPassBuilder::name());

        /** @var BoardingPassBuilder */
        return $this->builder();
    }

    public function couponPassBuilder(): CouponPassBuilder
    {
        $this->assertBuilderName(CouponPassBuilder::name());

        /** @var CouponPassBuilder */
        return $this->builder();
    }

    public function genericPassBuilder(): GenericPassBuilder
    {
        $this->assertBuilderName(GenericPassBuilder::name());

        /** @var GenericPassBuilder */
        return $this->builder();
    }

    public function storeCardPassBuilder(): StoreCardPassBuilder
    {
        $this->assertBuilderName(StoreCardPassBuilder::name());

        /** @var StoreCardPassBuilder */
        return $this->builder();
    }

    protected function assertBuilderName(string $expected): void
    {
        if ($this->builder_name !== $expected) {
            throw new RuntimeException("Expected pass builder [{$expected}], but this pass uses [{$this->builder_name}].");
        }
    }

    public function builder(): ApplePassBuilder
    {
        /** @var class-string<ApplePassBuilder> $builderClass */
        $builderClass = Config::getPassBuilderClass($this->builder_name, $this->platform);

        return $builderClass::make($this->content, $this->images, $this);
    }

    public function generate(): string
    {
        return $this->builder()->generate();
    }

    public function download(?string $name = null): DownloadableMobilePass
    {
        if ($this->platform !== Platform::Apple) {
            throw CannotDownload::wrongPlatform($this);
        }

        return new DownloadableMobilePass($this->generate(), $this->downloadName($name));
    }

    public function wasUpdatedAfter(?Carbon $since = null): bool
    {
        if (! $since) {
            return true;
        }

        return $this->updated_at > $since;
    }

    public function toResponse($request): Response
    {
        return $this->download($this->download_name)->toResponse($request);
    }

    public function toMailAttachment(): Attachment
    {
        return Attachment::fromData(fn () => $this->generate(), $this->downloadName().'.pkpass')
            ->withMime('application/vnd.apple.pkpass');
    }

    protected function downloadName(?string $name = null): string
    {
        $name = $name ?? $this->download_name ?? 'pass';

        return Str::beforeLast($name, '.pkpass');
    }
}
