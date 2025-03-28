<?php

namespace Spatie\LaravelMobilePass\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Builders\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Support\Config;

it('will throw an exception if an invalid model is used', function () {
    config()->set('mobile-pass.models.mobile_pass', Model::class);

    Config::mobilePassModel();
})->throws(InvalidConfig::class);

it('will throw an exception if an invalid action is used', function () {
    config()->set('mobile-pass.actions.notify_apple_of_pass_update', Model::class);

    Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);
})->throws(InvalidConfig::class);

it('can get a pass builder class', function () {
    $class = Config::getPassBuilderClass('airline');
    expect($class)->toBe(AirlinePassBuilder::class);
});

it('will throw an exception for a non-existing pass builder class', function () {
    Config::getPassBuilderClass('non-existing');
})->throws(InvalidConfig::class);

test('all configured builders are valid', function () {
    $builderNames = array_keys(config('mobile-pass.builders'));

    expect($builderNames)->toBeGreaterThan(0);

    foreach ($builderNames as $name) {
        $class = Config::getPassBuilderClass($name);

        expect($class)->not->toBeNull();
    }
});
