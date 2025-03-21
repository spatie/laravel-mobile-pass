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

it('returns 304 if pass was not updated after given time', function () {
    $pass = MobilePass::factory()->withIconImage()->create();

    $this
        ->withoutMiddleware()
        ->withHeaders([
            'If-Modified-Since' => $pass->updated_at->toRfc7231String(),
        ])
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->getKey(),
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertNotModified();
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
