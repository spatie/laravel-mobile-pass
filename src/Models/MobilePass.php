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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\Apple\ApplePassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\CannotDownload;
use Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Support\Apple\DownloadableMobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property string $pass_serial
 * @property string $builder_name
 * @property Platform $platform
 * @property array $images
 * @property array $content
 * @property ?string $download_name
 * @property Carbon $updated_at
 * @property ?Carbon $expired_at
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

    /** @return MorphTo<Model, $this> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<AppleMobilePassRegistration, $this> */
    public function registrations(): HasMany
    {
        $modelClass = Config::appleMobilePassRegistrationModel();

        return $this->hasMany($modelClass, 'mobile_pass_id');
    }

    public function devices(): HasManyThrough
    {
        $modelClass = Config::appleMobilePassRegistrationModel();
        $deviceModelClass = Config::appleDeviceModel();

        return $this->hasManyThrough($deviceModelClass, $modelClass, 'mobile_pass_id', 'id', 'id', 'device_id');
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

        return $latest?->event_type === 'save';
    }

    public function isCurrentlyInWallet(): bool
    {
        return match ($this->platform) {
            Platform::Apple => $this->registrations()->exists(),
            Platform::Google => $this->isCurrentlySavedToGoogleWallet(),
        };
    }

    public function isApple(): bool
    {
        return $this->platform === Platform::Apple;
    }

    public function isGoogle(): bool
    {
        return $this->platform === Platform::Google;
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

        $jwt = app(GoogleJwtSigner::class)->signSaveUrlJwt([
            "{$objectResource}s" => [['id' => $this->content['googleObjectId']]],
        ]);

        return "https://pay.google.com/gp/v/save/{$jwt}";
    }

    public function builder(): ApplePassBuilder
    {
        if ($this->platform !== Platform::Apple) {
            throw PlatformDoesntSupport::cannotUpdateFields($this->platform);
        }

        /** @var class-string<ApplePassBuilder> $builderClass */
        $builderClass = Config::getPassBuilderClass($this->builder_name, $this->platform);

        return $builderClass::hydrate($this);
    }

    public function updateField(
        string $key,
        string $value,
        ?string $changeMessage = null,
        ?string $label = null,
    ): static {
        if ($this->platform !== Platform::Apple) {
            throw PlatformDoesntSupport::cannotUpdateFields($this->platform);
        }

        $this->builder()
            ->updateField($key, $value, $changeMessage, $label)
            ->save();

        return $this;
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
        if ($since === null) {
            return true;
        }

        return $this->updated_at > $since;
    }

    /**
     * Make the pass directly returnable from a controller. Apple passes are served as
     * the signed `.pkpass` download; Google passes redirect to the Google Wallet save URL.
     */
    public function toResponse($request): Response
    {
        return match ($this->platform) {
            Platform::Apple => $this->download($this->download_name)->toResponse($request),
            Platform::Google => redirect($this->addToWalletUrl()),
        };
    }

    public function toMailAttachment(): Attachment
    {
        return Attachment::fromData(fn () => $this->generate(), $this->downloadName().'.pkpass')
            ->withMime('application/vnd.apple.pkpass');
    }

    protected function downloadName(?string $name = null): string
    {
        return Str::beforeLast($name ?? $this->download_name ?? 'pass', '.pkpass');
    }
}
