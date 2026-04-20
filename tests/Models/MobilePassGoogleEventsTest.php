<?php

use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('isCurrentlySavedToGoogleWallet returns true when latest event is save', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);

    GoogleMobilePassEvent::factory()->create([
        'mobile_pass_id' => $pass->id,
        'event_type' => 'save',
        'received_at' => now()->subDay(),
    ]);

    expect($pass->isCurrentlySavedToGoogleWallet())->toBeTrue();
});

it('isCurrentlySavedToGoogleWallet returns false when latest event is remove', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);

    GoogleMobilePassEvent::factory()->create([
        'mobile_pass_id' => $pass->id,
        'event_type' => 'save',
        'received_at' => now()->subDays(2),
    ]);

    GoogleMobilePassEvent::factory()->create([
        'mobile_pass_id' => $pass->id,
        'event_type' => 'remove',
        'received_at' => now()->subDay(),
    ]);

    expect($pass->isCurrentlySavedToGoogleWallet())->toBeFalse();
});

it('isCurrentlySavedToGoogleWallet returns false when there are no events', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);

    expect($pass->isCurrentlySavedToGoogleWallet())->toBeFalse();
});
