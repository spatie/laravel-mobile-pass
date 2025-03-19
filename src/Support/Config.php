<?php

namespace Spatie\LaravelMobilePass\Support;

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class Config
{
    /** @return class-string<\Spatie\LaravelMobilePass\Models\MobilePass> */
    public static function mobilePassModel(): string
    {
        return self::getModelClass('mobile_pass', MobilePass::class);
    }

    /** @return class-string<\Spatie\LaravelMobilePass\Models\MobilePass> */
    public static function modelPassRegistrationModel(): string
    {
        return self::getModelClass('mobile_pass_registration', MobilePassRegistration::class);
    }

    protected static function getModelClass(string $modelName, string $defaultClass): string
    {
        $modelClass = config("mobile-pass.models.{$modelName}", $defaultClass);

        if (! is_a($modelClass, $defaultClass, true)) {
            throw InvalidConfig::invalidModel($modelName, $modelClass, $defaultClass);
        }

        return $modelClass;
    }

    /**
     * @param string $actionName
     * @param class-string $shouldBeOrExtend
     *
     * @return class-string
     */
    public static function getActionClass(string $actionName, string $shouldBeOrExtend): string
    {
        $actionClass =  config("mobile-pass.actions.{$actionName}");

        if (! is_a($actionClass, $shouldBeOrExtend, true)) {
            throw InvalidConfig::invalidAction($actionName, $actionClass, $shouldBeOrExtend);
        }

        return $actionClass;
    }
}
