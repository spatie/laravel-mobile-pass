<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\EventDateInfo;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\EventType;
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

it('compiles the poster visual appearance fields', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->usePosterLayout()
        ->setFooterBackgroundColor('#642c00')
        ->setSuppressHeaderDarkening(true)
        ->setUseAutomaticColors(true)
        ->data();

    expect($compiledData['footerBackgroundColor'])->toBe('rgb(100, 44, 0)');
    expect($compiledData['suppressHeaderDarkening'])->toBeTrue();
    expect($compiledData['useAutomaticColors'])->toBeTrue();
});

it('omits the poster visual appearance fields when not set', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKeys([
        'footerBackgroundColor',
        'suppressHeaderDarkening',
        'useAutomaticColors',
    ]);
});

it('round-trips the poster visual appearance fields through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setFooterBackgroundColor('#642c00')
        ->setSuppressHeaderDarkening(true)
        ->setUseAutomaticColors(true)
        ->save();

    $data = $model->builder()->data();

    expect($data['footerBackgroundColor'])->toBe('rgb(100, 44, 0)');
    expect($data['suppressHeaderDarkening'])->toBeTrue();
    expect($data['useAutomaticColors'])->toBeTrue();
});

it('compiles the poster-only URL and metadata fields', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setAccessibilityURL('https://example.com/accessibility')
        ->setAddOnURL('https://example.com/add-on')
        ->setAuxiliaryStoreIdentifiers(111, 222)
        ->setBagPolicyURL('https://example.com/bag-policy')
        ->setContactVenueEmail('venue@example.com')
        ->setContactVenuePhoneNumber('+1-555-0100')
        ->setContactVenueWebsite('https://example.com/venue')
        ->setDirectionsInformationURL('https://example.com/directions')
        ->setEventLogoText('Fab Four Promotions')
        ->setMerchandiseURL('https://example.com/merch')
        ->setOrderFoodURL('https://example.com/food')
        ->setParkingInformationURL('https://example.com/parking-info')
        ->setPurchaseParkingURL('https://example.com/parking-purchase')
        ->setSellURL('https://example.com/sell')
        ->setTransferURL('https://example.com/transfer')
        ->setTransitInformationURL('https://example.com/transit')
        ->data();

    expect($compiledData)->toMatchArray([
        'accessibilityURL' => 'https://example.com/accessibility',
        'addOnURL' => 'https://example.com/add-on',
        'auxiliaryStoreIdentifiers' => [111, 222],
        'bagPolicyURL' => 'https://example.com/bag-policy',
        'contactVenueEmail' => 'venue@example.com',
        'contactVenuePhoneNumber' => '+1-555-0100',
        'contactVenueWebsite' => 'https://example.com/venue',
        'directionsInformationURL' => 'https://example.com/directions',
        'eventLogoText' => 'Fab Four Promotions',
        'merchandiseURL' => 'https://example.com/merch',
        'orderFoodURL' => 'https://example.com/food',
        'parkingInformationURL' => 'https://example.com/parking-info',
        'purchaseParkingURL' => 'https://example.com/parking-purchase',
        'sellURL' => 'https://example.com/sell',
        'transferURL' => 'https://example.com/transfer',
        'transitInformationURL' => 'https://example.com/transit',
    ]);
});

it('omits the poster-only URL and metadata fields when not set', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKeys([
        'accessibilityURL',
        'addOnURL',
        'auxiliaryStoreIdentifiers',
        'bagPolicyURL',
        'contactVenueEmail',
        'contactVenuePhoneNumber',
        'contactVenueWebsite',
        'directionsInformationURL',
        'eventLogoText',
        'merchandiseURL',
        'orderFoodURL',
        'parkingInformationURL',
        'purchaseParkingURL',
        'sellURL',
        'transferURL',
        'transitInformationURL',
    ]);
});

it('round-trips the poster-only URL and metadata fields through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setAccessibilityURL('https://example.com/accessibility')
        ->setAuxiliaryStoreIdentifiers(111, 222)
        ->setEventLogoText('Fab Four Promotions')
        ->save();

    $data = $model->builder()->data();

    expect($data['accessibilityURL'])->toBe('https://example.com/accessibility');
    expect($data['auxiliaryStoreIdentifiers'])->toBe([111, 222]);
    expect($data['eventLogoText'])->toBe('Fab Four Promotions');
});

it('compiles venue semantics into the semantics payload', function () {
    $compiledData = builderWithEveryVenueDetail()->data();

    expect($compiledData['semantics'])->toMatchArray(expectedVenueSemantics());
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
    $model = builderWithEveryVenueDetail()->save();

    expect($model->builder()->data()['semantics'])->toMatchArray(expectedVenueSemantics());
});

it('keeps only the coordinates of a venue location', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueLocation(Location::make(52.3143, 4.9416, 12.0, 'See you there'))
        ->data();

    expect($compiledData['semantics']['venueLocation'])->toBe([
        'latitude' => 52.3143,
        'longitude' => 4.9416,
    ]);
});

it('bundles a venue map image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueMapImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('venueMap.png'))->toBeTrue()
        ->and($reader->containsFile('venueMap@2x.png'))->toBeTrue()
        ->and($reader->containsFile('venueMap@3x.png'))->toBeTrue();
});

it('registers a remote venue map image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteVenueMapImage('https://example.com/pass/venue-map.png')
        ->save();

    expect($pass->images['venueMap'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/venue-map.png',
            'isRemote' => true,
        ]);
});

it('compiles event semantics into the semantics payload', function () {
    $compiledData = builderWithEveryEventDetail()->data();

    expect($compiledData['semantics'])->toMatchArray(expectedEventDetailSemantics());
});

it('round-trips event semantics through save and hydrate', function () {
    $model = builderWithEveryEventDetail()->save();

    expect($model->builder()->data()['semantics'])->toMatchArray(expectedEventDetailSemantics());
});

it('keeps event semantics that are falsy but meaningful', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setTailgatingAllowed(false)
        ->setDuration(0)
        ->data();

    expect($compiledData['semantics'])->toMatchArray([
        'tailgatingAllowed' => false,
        'duration' => 0,
    ]);
});

it('has a name', function () {
    expect(EventTicketPassBuilder::name())->toBe('event_ticket');
});

function builderWithEveryVenueDetail(): EventTicketPassBuilder
{
    return EventTicketPassBuilder::make()
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
        ->setVenueParkingLotsOpenDate(Carbon::parse('2026-08-19T15:00:00+00:00'));
}

function expectedVenueSemantics(): array
{
    return [
        'venueName' => 'Amsterdam ArenA',
        'venueLocation' => ['latitude' => 52.3143, 'longitude' => 4.9416],
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
    ];
}

function builderWithEveryEventDetail(): EventTicketPassBuilder
{
    return EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setEventName('Beatles Live at Shea')
        ->setEventType(EventType::LivePerformance)
        ->setEventStartDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setEventStartDateInfo(EventDateInfo::make(
            date: Carbon::parse('2026-08-19T18:00:00+00:00'),
            ignoreTimeComponents: false,
            timeZone: 'America/New_York',
            unannounced: false,
            undetermined: false,
        ))
        ->setEventEndDate(Carbon::parse('2026-08-19T23:00:00+00:00'))
        ->setAdmissionLevel('General Admission')
        ->setAdmissionLevelAbbreviation('GA')
        ->setAttendeeName('Dan Johnson')
        ->setAdditionalTicketAttributes('VIP parking included')
        ->setEntranceDescription('Gate A')
        ->setGenre('Rock')
        ->setTailgatingAllowed(true)
        ->setDuration(7200)
        ->setSilenceRequested(true)
        ->setPerformerNames('The Beatles', 'The Remains')
        ->setArtistIds('artist-1', 'artist-2')
        ->setAlbumIds('album-1')
        ->setPlaylistIds('playlist-1')
        ->setAwayTeamAbbreviation('NYY')
        ->setAwayTeamName('Yankees')
        ->setAwayTeamLocation('New York')
        ->setHomeTeamAbbreviation('BOS')
        ->setHomeTeamName('Red Sox')
        ->setHomeTeamLocation('Boston')
        ->setLeagueAbbreviation('MLB')
        ->setLeagueName('Major League Baseball')
        ->setSportName('Baseball')
        ->setSeats(Seat::make(
            aisle: 'A',
            number: '22',
            level: 'Lower Bowl',
            row: '8',
            section: 'B12',
            sectionColor: 'rgb(23,187,82)',
        ));
}

function expectedEventDetailSemantics(): array
{
    return [
        'eventName' => 'Beatles Live at Shea',
        'eventType' => 'PKEventTypeLivePerformance',
        'eventStartDate' => '2026-08-19T18:00:00+00:00',
        'eventStartDateInfo' => [
            'date' => '2026-08-19T18:00:00+00:00',
            'ignoreTimeComponents' => false,
            'timeZone' => 'America/New_York',
            'unannounced' => false,
            'undetermined' => false,
        ],
        'eventEndDate' => '2026-08-19T23:00:00+00:00',
        'admissionLevel' => 'General Admission',
        'admissionLevelAbbreviation' => 'GA',
        'attendeeName' => 'Dan Johnson',
        'additionalTicketAttributes' => 'VIP parking included',
        'entranceDescription' => 'Gate A',
        'genre' => 'Rock',
        'tailgatingAllowed' => true,
        'duration' => 7200,
        'silenceRequested' => true,
        'performerNames' => ['The Beatles', 'The Remains'],
        'artistIDs' => ['artist-1', 'artist-2'],
        'albumIDs' => ['album-1'],
        'playlistIDs' => ['playlist-1'],
        'awayTeamAbbreviation' => 'NYY',
        'awayTeamName' => 'Yankees',
        'awayTeamLocation' => 'New York',
        'homeTeamAbbreviation' => 'BOS',
        'homeTeamName' => 'Red Sox',
        'homeTeamLocation' => 'Boston',
        'leagueAbbreviation' => 'MLB',
        'leagueName' => 'Major League Baseball',
        'sportName' => 'Baseball',
        'seats' => [
            [
                'seatAisle' => 'A',
                'seatLevel' => 'Lower Bowl',
                'seatNumber' => '22',
                'seatRow' => '8',
                'seatSection' => 'B12',
                'seatSectionColor' => 'rgb(23,187,82)',
            ],
        ],
    ];
}
