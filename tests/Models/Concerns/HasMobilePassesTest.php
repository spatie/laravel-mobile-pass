<?php

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Models\TestModel;

beforeEach(function() {
    /** @var TestModel testModel */
   $this->testModel = TestModel::create();
});

it('can get all associated mobile passes', function() {
    $mobilePass = MobilePass::factory()->create();
    $this->testModel->addMobilePass($mobilePass);

    expect($this->testModel->mobilePasses)->toHaveCount(1);
});
