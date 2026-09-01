<?php

use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('compiles the boarding pass URL and metadata fields', function () {
    $compiledData = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setChangeSeatURL('https://example.com/change-seat')
        ->setEntertainmentURL('https://example.com/entertainment')
        ->setPurchaseAdditionalBaggageURL('https://example.com/baggage')
        ->setPurchaseLoungeAccessURL('https://example.com/lounge')
        ->setPurchaseWifiURL('https://example.com/wifi')
        ->setUpgradeURL('https://example.com/upgrade')
        ->setManagementURL('https://example.com/manage')
        ->setRegisterServiceAnimalURL('https://example.com/service-animal')
        ->setReportLostBagURL('https://example.com/lost-bag')
        ->setRequestWheelchairURL('https://example.com/wheelchair')
        ->setTrackBagsURL('https://example.com/track-bags')
        ->setTransitProviderEmail('provider@example.com')
        ->setTransitProviderPhoneNumber('+1-555-0100')
        ->setTransitProviderWebsiteURL('https://example.com/provider')
        ->data();

    expect($compiledData)->toMatchArray([
        'changeSeatURL' => 'https://example.com/change-seat',
        'entertainmentURL' => 'https://example.com/entertainment',
        'purchaseAdditionalBaggageURL' => 'https://example.com/baggage',
        'purchaseLoungeAccessURL' => 'https://example.com/lounge',
        'purchaseWifiURL' => 'https://example.com/wifi',
        'upgradeURL' => 'https://example.com/upgrade',
        'managementURL' => 'https://example.com/manage',
        'registerServiceAnimalURL' => 'https://example.com/service-animal',
        'reportLostBagURL' => 'https://example.com/lost-bag',
        'requestWheelchairURL' => 'https://example.com/wheelchair',
        'trackBagsURL' => 'https://example.com/track-bags',
        'transitProviderEmail' => 'provider@example.com',
        'transitProviderPhoneNumber' => '+1-555-0100',
        'transitProviderWebsiteURL' => 'https://example.com/provider',
    ]);
});

it('omits the boarding pass URL and metadata fields when not set', function () {
    $compiledData = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKeys([
        'changeSeatURL',
        'entertainmentURL',
        'purchaseAdditionalBaggageURL',
        'purchaseLoungeAccessURL',
        'purchaseWifiURL',
        'upgradeURL',
        'managementURL',
        'registerServiceAnimalURL',
        'reportLostBagURL',
        'requestWheelchairURL',
        'trackBagsURL',
        'transitProviderEmail',
        'transitProviderPhoneNumber',
        'transitProviderWebsiteURL',
    ]);
});

it('round-trips the boarding pass URL and metadata fields through save and hydrate', function () {
    $model = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setChangeSeatURL('https://example.com/change-seat')
        ->setTrackBagsURL('https://example.com/track-bags')
        ->setTransitProviderEmail('provider@example.com')
        ->save();

    $data = $model->builder()->data();

    expect($data['changeSeatURL'])->toBe('https://example.com/change-seat');
    expect($data['trackBagsURL'])->toBe('https://example.com/track-bags');
    expect($data['transitProviderEmail'])->toBe('provider@example.com');
});

it('round-trips the boarding pass URL fields through the uncompile path', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => AirlinePassBuilder::name(),
        'content' => [
            'organizationName' => 'My organization',
            'serialNumber' => '123456',
            'description' => 'Hello!',
            'changeSeatURL' => 'https://example.com/change-seat',
            'managementURL' => 'https://example.com/manage',
        ],
    ]);

    $data = AirlinePassBuilder::hydrate($model)->data();

    expect($data['changeSeatURL'])->toBe('https://example.com/change-seat');
    expect($data['managementURL'])->toBe('https://example.com/manage');
});
