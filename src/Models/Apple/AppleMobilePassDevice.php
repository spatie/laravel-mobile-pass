<?php

namespace Spatie\LaravelMobilePass\Models\Apple;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelMobilePass\Support\Config;

class AppleMobilePassDevice extends Model
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
