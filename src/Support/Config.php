<?php

namespace Spatie\LaravelMobilePass\Support;

use Spatie\LaravelMobilePass\Builders\Apple\PassBuilder as ApplePassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\PassBuilder as GooglePassBuilder;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

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
        return self::getModelClass('mobile_pass_registration', AppleMobilePassRegistration::class);
    }

    /** @return class-string<\Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice> */
    public static function deviceModel(): string
    {
        return self::getModelClass('mobile_pass_device', AppleMobilePassDevice::class);
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

    /** @return class-string<\Spatie\LaravelMobilePass\Builders\Apple\PassBuilder|\Spatie\LaravelMobilePass\Builders\Google\PassBuilder> */
    public static function getPassBuilderClass(string $passBuilderName, Platform $platform): string
    {
        $passBuilderClass = config("mobile-pass.builders.{$platform->value}.{$passBuilderName}");

        $classToExtend = match ($platform) {
            Platform::Apple => ApplePassBuilder::class,
            Platform::Google => GooglePassBuilder::class,
        };

        if (! $passBuilderClass) {
            throw InvalidConfig::passBuilderNotRegistered($passBuilderName, $platform);
        }

        if (! class_exists($passBuilderClass)) {
            throw InvalidConfig::passBuilderNotFound($passBuilderName, $passBuilderClass);
        }

        if (! is_a($passBuilderClass, $classToExtend, true)) {
            throw InvalidConfig::invalidPassBuilderClass($passBuilderName, $passBuilderClass, $platform);
        }

        return $passBuilderClass;
    }
}
