<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Spatie\LaravelMobilePass\Enums\Platform;

class InvalidConfig extends Exception
{
    public static function invalidModel($modelName, $modelClass, $defaultClass): self
    {
        return new static("The `{$modelName}` model must be an instance of `{$defaultClass}`. `{$modelClass}` does not extend {$defaultClass}.");
    }

    public static function invalidAction(string $actionName, mixed $actionClass, string $shouldBeOrExtend): self
    {
        return new static("The `{$actionName}` action must be an instance of `{$shouldBeOrExtend}`. `{$actionClass}` does not extend {$shouldBeOrExtend}.");
    }

    public static function invalidEvent(string $eventName, mixed $eventClass, string $shouldBeOrExtend): self
    {
        return new static("The `{$eventName}` event must be an instance of `{$shouldBeOrExtend}`. `{$eventClass}` does not extend {$shouldBeOrExtend}.");
    }

    public static function passBuilderNotRegistered(string $passBuilderName)
    {
        return new static("The pass builder `{$passBuilderName}` is not registered. Make sure you have registered it in the `builders` key of the  `mobile-pass` config file.");
    }

    public static function passBuilderNotFound(string $passBuilderName, mixed $passBuilderClass)
    {
        return new static("The pass builder `{$passBuilderName}` was not found. Make sure the class `{$passBuilderClass}` exists.");
    }

    public static function invalidPassBuilderClass(string $passBuilderName, mixed $passBuilderClass, Platform $platform)
    {
        $expectedNamespace = match ($platform) {
            Platform::Apple => 'Spatie\LaravelMobilePass\Builders\Apple',
            Platform::Google => 'Spatie\LaravelMobilePass\Builders\Google',
        };

        return new static("The pass builder `{$passBuilderName}` must be an instance of `{$expectedNamespace}\PassBuilder`. `{$passBuilderClass}` does not extend `{$expectedNamespace}\PassBuilder`.");
    }
}
