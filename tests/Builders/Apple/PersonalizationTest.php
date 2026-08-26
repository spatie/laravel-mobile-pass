<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Personalization;
use Spatie\LaravelMobilePass\Enums\PersonalizationField;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassPersonalization;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

function personalizableBuilder(): EventTicketPassBuilder
{
    return EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Rewards')
        ->setSerialNumber('BTL-REWARDS-0001')
        ->setDescription('Beatles Rewards Card')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setNfc(
            message: 'REWARDS-0001',
            encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE',
        )
        ->setPersonalization(Personalization::make(
            description: 'Sign up to earn points.',
            requiredPersonalizationFields: [PersonalizationField::Name, PersonalizationField::EmailAddress],
        ))
        ->setPersonalizationLogo(getTestSupportPath('images/spatie-thumbnail.png'));
}

it('throws when personalization is set without NFC', function () {
    EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Rewards')
        ->setSerialNumber('BTL-REWARDS-0002')
        ->setDescription('Beatles Rewards Card')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setPersonalization(Personalization::make(
            description: 'Sign up to earn points.',
            requiredPersonalizationFields: [PersonalizationField::Name],
        ))
        ->data();
})->throws(InvalidConfig::class, 'NFC');

it('persists the personalization config on save', function () {
    $pass = personalizableBuilder()->save();

    $this->assertDatabaseHas('apple_mobile_pass_personalizations', [
        'mobile_pass_id' => $pass->getKey(),
        'description' => 'Sign up to earn points.',
    ]);

    $personalization = AppleMobilePassPersonalization::where('mobile_pass_id', $pass->getKey())->first();
    expect($personalization->required_fields)->toBe([
        'PKPassPersonalizationFieldName',
        'PKPassPersonalizationFieldEmailAddress',
    ]);
});

it('bundles personalization.json and the logo while pending', function () {
    $pass = personalizableBuilder()->save();

    $reader = PkPassReader::fromString($pass->generate());

    expect($reader->containsFile('personalization.json'))->toBeTrue();
    expect($reader->containsFile('personalizationLogo.png'))->toBeTrue();

    $personalizationJson = json_decode($reader->fileContent('personalization.json'), true);
    expect($personalizationJson['description'])->toBe('Sign up to earn points.');
});

it('omits personalization.json and the logo once personalized', function () {
    $pass = personalizableBuilder()->save();

    AppleMobilePassPersonalization::where('mobile_pass_id', $pass->getKey())
        ->update(['personalized_at' => now()]);

    $reader = PkPassReader::fromString($pass->fresh()->generate());

    expect($reader->containsFile('personalization.json'))->toBeFalse();
    expect($reader->containsFile('personalizationLogo.png'))->toBeFalse();
});
