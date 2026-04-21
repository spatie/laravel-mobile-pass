<?php

use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Models\TestModel;

beforeEach(function () {
    /** @var TestModel testModel */
    $this->testModel = TestModel::create();
});

it('can get all associated mobile passes', function () {
    $mobilePass = MobilePass::factory()->create();
    $this->testModel->addMobilePass($mobilePass);

    expect($this->testModel->mobilePasses)->toHaveCount(1);
});

it('can get the first pass of a given type', function () {
    expect($this->testModel->refresh()->firstMobilePass())
        ->toBeNull();

    $mobilePass = MobilePass::factory()->create();
    $this->testModel->addMobilePass($mobilePass);

    expect($this->testModel->refresh()->firstMobilePass(PassType::Generic))
        ->not()->toBeNull();

    expect($this->testModel->refresh()->firstMobilePass(PassType::BoardingPass))
        ->toBeNull();
});

it('accepts a callable to filter the first pass', function () {
    $mobilePass = MobilePass::factory()->create();
    $this->testModel->addMobilePass($mobilePass);

    expect($this->testModel->refresh()->firstMobilePass(filter: fn ($query) => $query->where('type', PassType::Generic)))
        ->not()->toBeNull();

    expect($this->testModel->refresh()->firstMobilePass(filter: fn ($query) => $query->where('type', PassType::BoardingPass)))
        ->toBeNull();
});

it('can filter passes by platform via the relations', function () {
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Apple]));
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Apple]));
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Google]));

    expect($this->testModel->refresh()->applePasses)->toHaveCount(2);
    expect($this->testModel->refresh()->googlePasses)->toHaveCount(1);
    expect($this->testModel->refresh()->mobilePasses)->toHaveCount(3);
});

it('can get the first pass of a given platform', function () {
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Apple]));
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Google]));

    expect($this->testModel->refresh()->firstApplePass()->platform)->toBe(Platform::Apple);
    expect($this->testModel->refresh()->firstGooglePass()->platform)->toBe(Platform::Google);
});

it('returns null when no pass of the requested platform exists', function () {
    $this->testModel->addMobilePass(MobilePass::factory()->create(['platform' => Platform::Apple]));

    expect($this->testModel->refresh()->firstGooglePass())->toBeNull();
});

it('accepts both a type and a platform on firstMobilePass', function () {
    $this->testModel->addMobilePass(MobilePass::factory()->create([
        'platform' => Platform::Apple,
        'type' => PassType::Generic,
    ]));
    $this->testModel->addMobilePass(MobilePass::factory()->create([
        'platform' => Platform::Google,
        'type' => PassType::Generic,
    ]));

    expect($this->testModel->refresh()->firstMobilePass(PassType::Generic, Platform::Apple)->platform)->toBe(Platform::Apple);
    expect($this->testModel->refresh()->firstMobilePass(PassType::Generic, Platform::Google)->platform)->toBe(Platform::Google);
    expect($this->testModel->refresh()->firstMobilePass(PassType::BoardingPass, Platform::Apple))->toBeNull();
});

it('narrows firstApplePass and firstGooglePass by pass type', function () {
    $this->testModel->addMobilePass(MobilePass::factory()->create([
        'platform' => Platform::Apple,
        'type' => PassType::Generic,
    ]));

    expect($this->testModel->refresh()->firstApplePass(PassType::Generic))->not()->toBeNull();
    expect($this->testModel->refresh()->firstApplePass(PassType::BoardingPass))->toBeNull();
});
