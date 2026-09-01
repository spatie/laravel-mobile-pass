<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Personalization;
use Spatie\LaravelMobilePass\Enums\PersonalizationField;

it('serializes description, required fields, and terms and conditions', function () {
    $personalization = Personalization::make(
        description: 'Enter your information to sign up and earn points.',
        requiredPersonalizationFields: [
            PersonalizationField::Name,
            PersonalizationField::EmailAddress,
        ],
        termsAndConditions: 'Terms apply.',
    );

    expect($personalization->toArray())->toBe([
        'requiredPersonalizationFields' => [
            'PKPassPersonalizationFieldName',
            'PKPassPersonalizationFieldEmailAddress',
        ],
        'description' => 'Enter your information to sign up and earn points.',
        'termsAndConditions' => 'Terms apply.',
    ]);
});

it('omits termsAndConditions when not set', function () {
    $personalization = Personalization::make(
        description: 'Sign up.',
        requiredPersonalizationFields: [PersonalizationField::PostalCode],
    );

    expect($personalization->toArray())->toBe([
        'requiredPersonalizationFields' => ['PKPassPersonalizationFieldPostalCode'],
        'description' => 'Sign up.',
    ]);
});

it('hydrates from an array', function () {
    $personalization = Personalization::fromArray([
        'description' => 'Sign up.',
        'requiredPersonalizationFields' => ['PKPassPersonalizationFieldPhoneNumber'],
        'termsAndConditions' => 'Terms apply.',
    ]);

    expect($personalization->description)->toBe('Sign up.');
    expect($personalization->requiredPersonalizationFields)->toBe([PersonalizationField::PhoneNumber]);
    expect($personalization->termsAndConditions)->toBe('Terms apply.');
});
