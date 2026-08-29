<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serialises logoText, suppressStripShine, appLaunchURL, associatedStoreIdentifiers, and groupingIdentifier', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setLogoText('Fab Four Promotions')
        ->setSuppressStripShine(true)
        ->setAppLaunchURL('fabfour://launch')
        ->setAssociatedStoreIdentifiers(123456789, 987654321)
        ->setGroupingIdentifier('shea-stadium-1965')
        ->data();

    expect($data['logoText'])->toBe('Fab Four Promotions');
    expect($data['suppressStripShine'])->toBeTrue();
    expect($data['appLaunchURL'])->toBe('fabfour://launch');
    expect($data['associatedStoreIdentifiers'])->toBe([123456789, 987654321]);
    expect($data['groupingIdentifier'])->toBe('shea-stadium-1965');
});

it('omits logoText, appLaunchURL, associatedStoreIdentifiers, and groupingIdentifier when not set', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->not->toHaveKeys([
        'logoText',
        'suppressStripShine',
        'appLaunchURL',
        'associatedStoreIdentifiers',
        'groupingIdentifier',
    ]);
});

it('round-trips logoText, appLaunchURL, associatedStoreIdentifiers, and groupingIdentifier through the uncompile path', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'logoText' => 'Fab Four Promotions',
            'appLaunchURL' => 'fabfour://launch',
            'associatedStoreIdentifiers' => [123456789],
            'groupingIdentifier' => 'shea-stadium-1965',
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['logoText'])->toBe('Fab Four Promotions');
    expect($data['appLaunchURL'])->toBe('fabfour://launch');
    expect($data['associatedStoreIdentifiers'])->toBe([123456789]);
    expect($data['groupingIdentifier'])->toBe('shea-stadium-1965');
});

it('always includes passType in userInfo, even without setUserInfo', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['userInfo'])->toBe(['passType' => 'eventTicket']);
});

it('merges custom userInfo data alongside passType', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setUserInfo(['favoriteDrink' => 'tea'])
        ->setUserInfo(['favoriteSandwich' => 'club'])
        ->data();

    expect($data['userInfo'])->toBe([
        'passType' => 'eventTicket',
        'favoriteDrink' => 'tea',
        'favoriteSandwich' => 'club',
    ]);
});

it('round-trips custom userInfo data without duplicating passType', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'userInfo' => [
                'passType' => 'eventTicket',
                'favoriteDrink' => 'tea',
            ],
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['userInfo'])->toBe([
        'passType' => 'eventTicket',
        'favoriteDrink' => 'tea',
    ]);
});
