<?php

namespace Spatie\LaravelMobilePass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelMobilePass\Enums\PassType;
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

    public function firstMobilePass(?callable $callable = null): ?MobilePass
    {
        $query = $this->mobilePasses();

        $query->when($callable, $callable($query));

        return $query->first();
    }

    public function firstMobilePassOfType(PassType $passType, ?callable $callable = null): ?MobilePass
    {
        return $this->firstMobilePass(function ($query) use ($passType, $callable) {
            $query->where('passType', $passType->value);

            if ($callable) {
                $callable($query);
            }
        });
    }
}
