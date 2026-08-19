<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('builds a basic event ticket', function () {
    $eventTicketPassBuilder = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addHeaderField('event', 'Laracon EU')
        ->addField('venue', 'Amsterdam')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addAuxiliaryField('seat', 'A12')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'));

    $compiledData = $eventTicketPassBuilder->data();

    expect($compiledData)->toHaveKey('eventTicket');
    expect($compiledData['eventTicket'])->toHaveKeys([
        'primaryFields',
        'secondaryFields',
        'headerFields',
        'auxiliaryFields',
    ]);

    $generatedPass = $eventTicketPassBuilder->generate();

    expect($generatedPass)->toMatchMobilePassSnapshot();
});

it('bundles a background image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setBackgroundImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('background.png'))->toBeTrue()
        ->and($reader->containsFile('background@2x.png'))->toBeTrue()
        ->and($reader->containsFile('background@3x.png'))->toBeTrue();
});

it('registers a remote background image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteBackgroundImage('https://example.com/pass/background.png')
        ->save();

    expect($pass->images['background'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/background.png',
            'isRemote' => true,
        ]);
});

it('bundles an artwork image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setArtworkImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('artwork.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@2x.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@3x.png'))->toBeTrue();
});

it('registers a remote artwork image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteArtworkImage('https://example.com/pass/artwork.png')
        ->save();

    expect($pass->images['artwork'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/artwork.png',
            'isRemote' => true,
        ]);
});

it('activates the poster layout via preferredStyleSchemes', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->usePosterLayout()
        ->data();

    expect($compiledData['preferredStyleSchemes'])->toBe(['posterEventTicket', 'eventTicket']);
});

it('omits preferredStyleSchemes when the poster layout is not used', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('preferredStyleSchemes');
});

it('round-trips preferredStyleSchemes through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->usePosterLayout()
        ->save();

    expect($model->builder()->data()['preferredStyleSchemes'])
        ->toBe(['posterEventTicket', 'eventTicket']);
});

it('has a name', function () {
    expect(EventTicketPassBuilder::name())->toBe('event_ticket');
});

it('compiles venue semantics into the semantics payload', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueName('Amsterdam ArenA')
        ->setVenueLocation(Location::make(52.3143, 4.9416))
        ->setVenueEntrance('Gate A')
        ->setVenueEntranceDoor('Door 3')
        ->setVenueEntranceGate('Gate A')
        ->setVenueEntrancePortal('Portal 1')
        ->setVenuePhoneNumber('+31 20 311 1333')
        ->setVenueRoom('Main Hall')
        ->setVenueRegionName('Amsterdam')
        ->setVenueOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueCloseDate(Carbon::parse('2026-08-19T23:00:00+00:00'))
        ->setVenueDoorsOpenDate(Carbon::parse('2026-08-19T18:30:00+00:00'))
        ->setVenueGatesOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueFanZoneOpenDate(Carbon::parse('2026-08-19T17:00:00+00:00'))
        ->setVenueBoxOfficeOpenDate(Carbon::parse('2026-08-19T16:00:00+00:00'))
        ->setVenueParkingLotsOpenDate(Carbon::parse('2026-08-19T15:00:00+00:00'))
        ->data();

    expect($compiledData['semantics'])->toMatchArray([
        'venueName' => 'Amsterdam ArenA',
        'venueEntrance' => 'Gate A',
        'venueEntranceDoor' => 'Door 3',
        'venueEntranceGate' => 'Gate A',
        'venueEntrancePortal' => 'Portal 1',
        'venuePhoneNumber' => '+31 20 311 1333',
        'venueRoom' => 'Main Hall',
        'venueRegionName' => 'Amsterdam',
        'venueOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueCloseDate' => '2026-08-19T23:00:00+00:00',
        'venueDoorsOpenDate' => '2026-08-19T18:30:00+00:00',
        'venueGatesOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueFanZoneOpenDate' => '2026-08-19T17:00:00+00:00',
        'venueBoxOfficeOpenDate' => '2026-08-19T16:00:00+00:00',
        'venueParkingLotsOpenDate' => '2026-08-19T15:00:00+00:00',
    ]);
    expect($compiledData['semantics']['venueLocation'])->toMatchArray([
        'latitude' => 52.3143,
        'longitude' => 4.9416,
    ]);
});

it('omits venue semantics when none are set', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('semantics');
});

it('round-trips venue semantics through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueName('Amsterdam ArenA')
        ->setVenueLocation(Location::make(52.3143, 4.9416))
        ->setVenueEntrance('Gate A')
        ->setVenueEntranceDoor('Door 3')
        ->setVenueEntranceGate('Gate A')
        ->setVenueEntrancePortal('Portal 1')
        ->setVenuePhoneNumber('+31 20 311 1333')
        ->setVenueRoom('Main Hall')
        ->setVenueRegionName('Amsterdam')
        ->setVenueOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueCloseDate(Carbon::parse('2026-08-19T23:00:00+00:00'))
        ->setVenueDoorsOpenDate(Carbon::parse('2026-08-19T18:30:00+00:00'))
        ->setVenueGatesOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueFanZoneOpenDate(Carbon::parse('2026-08-19T17:00:00+00:00'))
        ->setVenueBoxOfficeOpenDate(Carbon::parse('2026-08-19T16:00:00+00:00'))
        ->setVenueParkingLotsOpenDate(Carbon::parse('2026-08-19T15:00:00+00:00'))
        ->save();

    $hydratedData = $model->builder()->data();

    expect($hydratedData['semantics'])->toMatchArray([
        'venueName' => 'Amsterdam ArenA',
        'venueEntrance' => 'Gate A',
        'venueEntranceDoor' => 'Door 3',
        'venueEntranceGate' => 'Gate A',
        'venueEntrancePortal' => 'Portal 1',
        'venuePhoneNumber' => '+31 20 311 1333',
        'venueRoom' => 'Main Hall',
        'venueRegionName' => 'Amsterdam',
        'venueOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueCloseDate' => '2026-08-19T23:00:00+00:00',
        'venueDoorsOpenDate' => '2026-08-19T18:30:00+00:00',
        'venueGatesOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueFanZoneOpenDate' => '2026-08-19T17:00:00+00:00',
        'venueBoxOfficeOpenDate' => '2026-08-19T16:00:00+00:00',
        'venueParkingLotsOpenDate' => '2026-08-19T15:00:00+00:00',
    ]);
    expect($hydratedData['semantics']['venueLocation'])->toMatchArray([
        'latitude' => 52.3143,
        'longitude' => 4.9416,
    ]);
});
