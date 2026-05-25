<?php

namespace Spatie\LaravelMobilePass\Tests\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Tests\TestSupport\Models\CustomMobilePass;

beforeEach(function () {
    Schema::create('custom_mobile_passes', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('pass_serial')->unique();
        $table->string('type');
        $table->string('platform');
        $table->string('builder_name');
        $table->json('content');
        $table->json('images');
        $table->string('download_name')->nullable();
        $table->nullableMorphs('model');
        $table->timestamp('expired_at')->nullable();
        $table->timestamps();
    });

    config()->set('mobile-pass.models.mobile_pass', CustomMobilePass::class);
});

it('saves an Apple pass to the configured mobile pass model', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber('custom-serial-123')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    expect($pass)->toBeInstanceOf(CustomMobilePass::class);

    $this->assertDatabaseHas('custom_mobile_passes', ['pass_serial' => 'custom-serial-123']);
    $this->assertDatabaseCount('mobile_passes', 0);
});

it('resolves the configured mobile pass model when checking for updates', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber('custom-serial-456')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});
