<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serialises a relevant date onto the pass', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setRelevantDate(Carbon::parse('1965-08-15 20:00', 'America/New_York'))
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->toHaveKey('relevantDate');
    expect($data['relevantDate'])->toStartWith('1965-08-15T20:00:00');
});

it('serialises locations and max distance onto the pass', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addLocation(latitude: 40.7559, longitude: -73.8456, relevantText: 'Welcome to Shea Stadium')
        ->addLocation(latitude: 40.7580, longitude: -73.9855)
        ->setMaxDistance(500)
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->toHaveKey('locations');
    expect($data['locations'])->toHaveCount(2);
    expect($data['locations'][0])->toMatchArray([
        'latitude' => 40.7559,
        'longitude' => -73.8456,
        'relevantText' => 'Welcome to Shea Stadium',
    ]);
    expect($data['locations'][1])->toMatchArray([
        'latitude' => 40.7580,
        'longitude' => -73.9855,
    ]);
    expect($data['maxDistance'])->toBe(500);
});

it('round-trips relevance data through the uncompile path', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'relevantDate' => '1965-08-15T20:00:00-04:00',
            'locations' => [
                ['latitude' => 40.7559, 'longitude' => -73.8456, 'relevantText' => 'Shea Stadium'],
            ],
            'maxDistance' => 750,
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['relevantDate'])->toStartWith('1965-08-15T20:00:00');
    expect($data['locations'])->toHaveCount(1);
    expect($data['locations'][0]['relevantText'])->toBe('Shea Stadium');
    expect($data['maxDistance'])->toBe(750);
});
