<?php

use Spatie\LaravelMobilePass\Enums\PassType;
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

it('accepts a callable to filter the first pass', function() {
    $mobilePass = MobilePass::factory()->create();
    $this->testModel->addMobilePass($mobilePass);


    expect($this->testModel->refresh()->firstMobilePass(filter: fn($query) => $query->where('type', PassType::Generic)))
        ->not()->toBeNull();

    expect($this->testModel->refresh()->firstMobilePass(filter: fn($query) => $query->where('type', PassType::BoardingPass)))
        ->toBeNull();
});
