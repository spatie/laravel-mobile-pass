<?php

namespace Spatie\LaravelMobilePass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/** @var $this \Illuminate\Database\Eloquent\Model */
trait HasMobilePasses
{
    public function addMobilePass(MobilePass $mobilePass): void
    {
        $this->mobilePasses()->save($mobilePass);
    }

    public function mobilePasses(): MorphMany
    {
        $mobilePassModel = Config::mobilePassModel();

        return $this->morphMany($mobilePassModel, 'model');
    }

    public function applePasses(): MorphMany
    {
        return $this->mobilePasses()->where('platform', Platform::Apple);
    }

    public function googlePasses(): MorphMany
    {
        return $this->mobilePasses()->where('platform', Platform::Google);
    }

    public function firstMobilePass(
        ?PassType $passType = null,
        ?Platform $platform = null,
        ?callable $filter = null,
    ): ?MobilePass {
        $query = $this->mobilePasses();

        if ($passType) {
            $query->where('type', $passType);
        }

        if ($platform) {
            $query->where('platform', $platform);
        }

        if ($filter) {
            $filter($query);
        }

        return $query->first();
    }

    public function firstApplePass(?PassType $passType = null): ?MobilePass
    {
        return $this->firstMobilePass($passType, Platform::Apple);
    }

    public function firstGooglePass(?PassType $passType = null): ?MobilePass
    {
        return $this->firstMobilePass($passType, Platform::Google);
    }
}
