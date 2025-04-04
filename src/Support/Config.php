<?php

namespace Spatie\LaravelMobilePass\Support;

use Spatie\LaravelMobilePass\Builders\Apple\PassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class Config
{
    /** @return class-string<\Spatie\LaravelMobilePass\Models\MobilePass> */
    public static function mobilePassModel(): string
    {
        return self::getModelClass('mobile_pass', MobilePass::class);
    }

    /** @return class-string<\Spatie\LaravelMobilePass\Models\MobilePass> */
    public static function mobilePassRegistrationModel(): string
    {
        return self::getModelClass('mobile_pass_registration', MobilePassRegistration::class);
    }

    /** @return class-string<\Spatie\LaravelMobilePass\Models\MobilePassDevice> */
    public static function deviceModel(): string
    {
        return self::getModelClass('mobile_pass_device', MobilePassDevice::class);
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
     * @param  class-string  $shouldBeOrExtend
     * @return class-string
     */
    public static function getActionClass(string $actionName, string $shouldBeOrExtend): string
    {
        $actionClass = config("mobile-pass.actions.{$actionName}");

        if (! is_a($actionClass, $shouldBeOrExtend, true)) {
            throw InvalidConfig::invalidAction($actionName, $actionClass, $shouldBeOrExtend);
        }

        return $actionClass;
    }

    /** @return class-string<\Spatie\LaravelMobilePass\Builders\Apple\PassBuilder> */
    public static function getApplePassBuilderClass(string $passBuilderName): string
    {
        $passBuilderClass = config("mobile-pass.builders.{$passBuilderName}");

        if (! $passBuilderClass) {
            throw InvalidConfig::passBuilderNotRegistered($passBuilderName);
        }

        if (! class_exists($passBuilderClass)) {
            throw InvalidConfig::passBuilderNotFound($passBuilderName, $passBuilderClass);
        }

        if (! is_a($passBuilderClass, PassBuilder::class, true)) {
            throw InvalidConfig::invalidPassBuilderClass($passBuilderName, $passBuilderClass, Platform::Apple);
        }

        return $passBuilderClass;
    }
}
