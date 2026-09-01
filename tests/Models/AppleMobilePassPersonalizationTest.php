<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassPersonalization;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('belongs to a mobile pass', function () {
    $pass = MobilePass::factory()->create();

    $personalization = AppleMobilePassPersonalization::factory()->create([
        'mobile_pass_id' => $pass->getKey(),
    ]);

    expect($personalization->pass->is($pass))->toBeTrue();
    expect($pass->personalization->is($personalization))->toBeTrue();
});

it('casts required_fields and submitted_info as arrays and personalized_at as a datetime', function () {
    $personalization = AppleMobilePassPersonalization::factory()->create([
        'required_fields' => ['PKPassPersonalizationFieldName'],
        'submitted_info' => ['fullName' => 'John Appleseed'],
        'personalized_at' => now(),
    ]);

    expect($personalization->required_fields)->toBe(['PKPassPersonalizationFieldName']);
    expect($personalization->submitted_info)->toBe(['fullName' => 'John Appleseed']);
    expect($personalization->personalized_at)->toBeInstanceOf(Carbon::class);
});
