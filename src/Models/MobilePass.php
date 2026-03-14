<?php

namespace Spatie\LaravelMobilePass\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Response;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\ApplePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\CannotDownload;
use Spatie\LaravelMobilePass\Support\Apple\DownloadableMobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $builder_name
 * @property \Spatie\LaravelMobilePass\Enums\Platform $platform
 * @property array $images
 * @property array $content
 * @property string|null $download_name
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration> $registrations
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
            /** @var class-string<NotifyAppleOfPassUpdateAction> $action */
            $action = Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);

            app($action)->execute($mobilePass);
        });
    }

    /** @return HasMany<\Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration, $this> */
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

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'content' => 'json',
            'images' => 'json',
        ];
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
            throw new \RuntimeException("Expected pass builder [{$expected}], but this pass uses [{$this->builder_name}].");
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
