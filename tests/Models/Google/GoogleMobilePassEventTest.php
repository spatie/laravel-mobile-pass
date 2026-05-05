<?php

use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;

it('filters by event type using scopes', function () {
    GoogleMobilePassEvent::factory()->create(['event_type' => 'save']);
    GoogleMobilePassEvent::factory()->create(['event_type' => 'remove']);
    GoogleMobilePassEvent::factory()->create(['event_type' => 'save']);

    expect(GoogleMobilePassEvent::saves()->count())->toBe(2);
    expect(GoogleMobilePassEvent::removes()->count())->toBe(1);
});
