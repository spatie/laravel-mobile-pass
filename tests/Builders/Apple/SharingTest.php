<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serialises sharing prohibited onto the pass', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setSharingProhibited(true)
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->toHaveKey('sharingProhibited');
    expect($data['sharingProhibited'])->toBeTrue();
});

it('round-trips sharing prohibited through the uncompile path', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'sharingProhibited' => true,
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['sharingProhibited'])->toBeTrue();
});
