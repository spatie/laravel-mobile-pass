<?php

namespace Spatie\LaravelMobilePass\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\PassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\CannotDownload;
use Spatie\LaravelMobilePass\Support\Apple\DownloadableMobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePass extends Model implements Attachable, Responsable
{
    use HasFactory;
    use HasUuids;

    public $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::updated(function (MobilePass $mobilePass) {
            /** @var class-string<NotifyAppleOfPassUpdateAction> $action */
            $action = Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);

            app($action)->execute($mobilePass);
        });
    }

    public function registrations(): HasMany
    {
        $modelClass = Config::mobilePassRegistrationModel();

        return $this->hasMany($modelClass, 'pass_serial');
    }

    public function devices(): HasManyThrough
    {
        $modelClass = Config::mobilePassRegistrationModel();
        $deviceModelClass = Config::deviceModel();

        return $this->hasManyThrough($deviceModelClass, $modelClass, 'pass_serial', 'id', 'id', 'device_id');
    }

    protected function casts()
    {
        return [
            'platform' => Platform::class,
            'content' => 'json',
            'images' => 'json',
        ];
    }

    public function airlinePassBuilder(): AirlinePassBuilder
    {
        return $this->builder();
    }

    // add other builder methods here

    public function builder(): PassBuilder
    {
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

    public function toResponse($request)
    {
        return $this->download($this->download_name)->toResponse($request);
    }

    public function toMailAttachment()
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
