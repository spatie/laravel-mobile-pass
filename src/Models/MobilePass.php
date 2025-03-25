<?php

namespace Spatie\LaravelMobilePass\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\BoardingPasses\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\PassBuilder;
use Spatie\LaravelMobilePass\Entities\Barcode;
use Spatie\LaravelMobilePass\Entities\Colour;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\TransitType;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\DownloadableMobilePass;

class MobilePass extends Model
{
    use HasFactory, HasUuids;

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
            'content' => 'json',
            'images' => 'json',
        ];
    }


    public function builder(): PassBuilder
    {
        /** @var class-string<PassBuilder> $builderClass */
        $builderClass = $this->builder_class;

        return $builderClass::make($this->content, $this->images);
    }

    public function generate(): string
    {
        return $this->builder()->generate();
    }

    public function download(string $name = 'pass'): DownloadableMobilePass
    {
        return new DownloadableMobilePass($this->generate(), $name);
    }

    public function wasUpdatedAfter(?Carbon $since = null): bool
    {
        if (! $since) {
            return true;
        }

        return $this->updated_at > $since;
    }
}
