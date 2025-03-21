<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\LaravelMobilePass\Events\MobilePassRegisteredEvent;
use Spatie\LaravelMobilePass\Events\MobilePassUnregisteredEvent;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePassRegistration extends Model
{
    use HasUuids, SoftDeletes;

    public $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::created(function (MobilePassRegistration $registration) {
            $eventClass = Config::getEventClass('mobile_pass_registered', MobilePassRegisteredEvent::class);

            event(new $eventClass($registration));
        });

        static::softDeleted(function (MobilePassRegistration $registration) {
            $eventClass = Config::getEventClass('mobile_pass_unregistered', MobilePassUnregisteredEvent::class);

            event(new $eventClass($registration));
        });
    }

    public function pass(): BelongsTo
    {
        $modelClass = Config::mobilePassModel();

        return $this->belongsTo($modelClass, 'pass_serial');
    }

    public function device(): BelongsTo
    {
        $modelClass = Config::deviceModel();

        return $this->belongsTo($modelClass, 'device_id');
    }

    public function appleUpdateUrl(): string
    {
        return config('mobile-pass.apple.apple_push_base_url')."/{$this->device->push_token}";
    }
}
