<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Personalization;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\PersonalizationField;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('bundles personalization.json until /personalize is called, then omits it', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Rewards')
        ->setSerialNumber('BTL-REWARDS-9000')
        ->setDescription('Beatles Rewards Card')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setNfc(
            message: 'REWARDS-9000',
            encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
        )
        ->setPersonalization(Personalization::make(
            description: 'Sign up to earn points.',
            requiredPersonalizationFields: [PersonalizationField::Name],
        ))
        ->setPersonalizationLogo(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    // Simulates the device downloading the pass in a fresh request — hydrate from DB only.
    $downloadedBeforeSignup = PkPassReader::fromString($pass->fresh()->generate());
    expect($downloadedBeforeSignup->containsFile('personalization.json'))->toBeTrue();

    // Personalization must be driven entirely by the presence/absence of personalization.json —
    // never by fields inside pass.json itself.
    expect($downloadedBeforeSignup->passProperties())->not->toHaveKey('personalizationRequired');
    expect($downloadedBeforeSignup->passProperties())->not->toHaveKey('personalizationToken');

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'wallet-issued-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ])
        ->assertSuccessful();

    $downloadedAfterSignup = PkPassReader::fromString($pass->fresh()->generate());
    expect($downloadedAfterSignup->containsFile('personalization.json'))->toBeFalse();
    expect($downloadedAfterSignup->containsFile('personalizationLogo.png'))->toBeFalse();
});

it('serves the personalized pass via the existing check-for-updates endpoint', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Rewards')
        ->setSerialNumber('BTL-REWARDS-9001')
        ->setDescription('Beatles Rewards Card')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setNfc(
            message: 'REWARDS-9001',
            encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
        )
        ->setPersonalization(Personalization::make(
            description: 'Sign up to earn points.',
            requiredPersonalizationFields: [PersonalizationField::Name],
        ))
        ->save();

    $beforeSignup = $pass->fresh()->updated_at;

    $this->travel(1)->minutes();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'wallet-issued-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ]);

    $response = $this
        ->withoutMiddleware()
        ->withHeaders(['If-Modified-Since' => $beforeSignup->toRfc7231String()])
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]));

    $response->assertSuccessful();

    // Prove that the normal Wallet update-check flow — not a special-cased response —
    // is what delivers the personalized pass: read the real HTTP response body.
    $reader = PkPassReader::fromString($response->getContent());
    expect($reader->containsFile('personalization.json'))->toBeFalse();
});
