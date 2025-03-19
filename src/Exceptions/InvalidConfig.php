<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;

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
}
