<?php

namespace Spatie\LaravelMobilePass\Models\Apple;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $push_token
 */
class AppleMobilePassDevice extends Model
{
    use HasFactory;

    public $guarded = [];

    public $incrementing = false;

    public function registrations(): HasMany
    {
        return $this->hasMany(Config::appleMobilePassRegistrationModel(), 'device_id');
    }
}
