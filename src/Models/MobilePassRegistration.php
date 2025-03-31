<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePassRegistration extends Model
{
    use HasFactory;
    use  HasUuids;

    public $guarded = [];

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
