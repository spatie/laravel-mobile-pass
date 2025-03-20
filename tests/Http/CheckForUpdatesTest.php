<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('returns the generated pass', function () {
    $pass = MobilePass::make()
        ->setIconImage(
            Image::make(
                getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

    $pass->save();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->getKey(),
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});

it('doesnt trigger an update to Apple', function () {
    $pass = MobilePass::make()
        ->setIconImage(
            Image::make(
                getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

    $pass->save();

    $this
        ->mock(NotifyAppleOfPassUpdateAction::class)
        ->makePartial()
        ->shouldNotReceive('execute');

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->getKey(),
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});