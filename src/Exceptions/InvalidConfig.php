<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Spatie\LaravelMobilePass\Enums\Platform;

class InvalidConfig extends Exception
{
    public static function invalidModel(string $modelName, string $modelClass, string $defaultClass): self
    {
        return new self("The `{$modelName}` model must be an instance of `{$defaultClass}`. `{$modelClass}` does not extend {$defaultClass}.");
    }

    public static function invalidAction(string $actionName, mixed $actionClass, string $shouldBeOrExtend): self
    {
        return new self("The `{$actionName}` action must be an instance of `{$shouldBeOrExtend}`. `{$actionClass}` does not extend {$shouldBeOrExtend}.");
    }

    public static function passBuilderNotRegistered(string $passBuilderName, Platform $platform): self
    {
        return new self("The pass builder `{$passBuilderName}` is not registered. Make sure you have registered it in the `builders.{$platform->value}` key of the  `mobile-pass` config file.");
    }

    public static function passBuilderNotFound(string $passBuilderName, mixed $passBuilderClass): self
    {
        return new self("The pass builder `{$passBuilderName}` was not found. Make sure the class `{$passBuilderClass}` exists.");
    }

    public static function invalidPassBuilderClass(string $passBuilderName, mixed $passBuilderClass, Platform $platform): self
    {
        $expectedNamespace = match ($platform) {
            Platform::Apple => 'Spatie\LaravelMobilePass\Builders\Apple',
            Platform::Google => 'Spatie\LaravelMobilePass\Builders\Google',
        };

        return new self("The pass builder `{$passBuilderName}` must be an instance of `{$expectedNamespace}\PassBuilder`. `{$passBuilderClass}` does not extend `{$expectedNamespace}\PassBuilder`.");
    }

    public static function missingGoogleCredentials(): self
    {
        return new self(
            'No Google service account key is configured. Set one of '
            .'MOBILE_PASS_GOOGLE_KEY_BASE64, MOBILE_PASS_GOOGLE_KEY_CONTENTS, '
            .'or MOBILE_PASS_GOOGLE_KEY_PATH. See the docs on getting credentials from Google.'
        );
    }

    public static function webserviceHostMustBeHttps(string $host): self
    {
        return new self(
            "The `mobile-pass.apple.webservice.host` config value must use HTTPS, got `{$host}`. "
            .'Apple rejects passes whose webServiceURL is not served over HTTPS. '
            .'Leave the value empty if you do not need device registrations (typical for local dev over http://).'
        );
    }
}
