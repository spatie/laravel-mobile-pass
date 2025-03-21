<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePassDevice extends Model
{
    use HasFactory;

    public $guarded = [];

    public $incrementing = false;

    public function registrations(): HasMany
    {
        $modelClass = Config::mobilePassRegistrationModel();

        return $this->hasMany($modelClass, 'device_id');
    }
}
