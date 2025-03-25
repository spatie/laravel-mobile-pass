<?php

namespace Spatie\LaravelMobilePass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/** @var $this \Illuminate\Database\Eloquent\Model */
trait HasMobilePasses
{
    public function mobilePasses(): MorphMany
    {
        $mobilePassModel = Config::mobilePassModel();

        return $this->morphMany($mobilePassModel, 'model');
    }

    public function addMobilePass(MobilePass $mobilePass)
    {
        $this->mobilePasses()->save($mobilePass);
    }
}
