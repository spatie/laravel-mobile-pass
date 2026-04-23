<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serialises an NFC payload onto the pass', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setNfc(
            message: 'TICKET-12345',
            encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
        )
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->toHaveKey('nfc');
    expect($data['nfc'])->toMatchArray([
        'message' => 'TICKET-12345',
        'encryptionPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
    ]);
    expect($data['nfc'])->not->toHaveKey('requiresAuthentication');
});

it('includes requiresAuthentication when set', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setNfc(
            message: 'TICKET-12345',
            encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
            requiresAuthentication: true,
        )
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['nfc']['requiresAuthentication'])->toBeTrue();
});

it('round-trips NFC data through the uncompile path', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'nfc' => [
                'message' => 'TICKET-12345',
                'encryptionPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
                'requiresAuthentication' => true,
            ],
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['nfc']['message'])->toBe('TICKET-12345');
    expect($data['nfc']['encryptionPublicKey'])->toBe('MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE');
    expect($data['nfc']['requiresAuthentication'])->toBeTrue();
});
