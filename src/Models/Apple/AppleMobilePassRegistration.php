<?php

namespace Spatie\LaravelMobilePass\Models\Apple;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $pass_type_id
 * @property string $pass_serial
 * @property string $device_id
 * @property MobilePass $pass
 * @property AppleMobilePassDevice $device
 */
class AppleMobilePassRegistration extends Model
{
    use HasFactory;
    use HasUuids;

    public $guarded = [];

    public function pass(): BelongsTo
    {
        return $this->belongsTo(Config::mobilePassModel(), 'pass_serial');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Config::appleDeviceModel(), 'device_id');
    }

    public function appleUpdateUrl(): string
    {
        $baseUrl = config('mobile-pass.apple.apple_push_base_url');

        return "{$baseUrl}/{$this->device->push_token}";
    }
}
