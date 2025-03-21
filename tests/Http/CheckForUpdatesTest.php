<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('returns the generated pass', function () {
    $pass = MobilePass::factory()->withIconImage()->create();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->getKey(),
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});

it('doesnt trigger an update to Apple', function () {
    $pass = MobilePass::factory()->withIconImage()->create();

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
