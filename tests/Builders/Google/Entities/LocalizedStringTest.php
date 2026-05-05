<?php

use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;

it('builds a default-value localized string', function () {
    $localized = LocalizedString::of('The Eras Tour');

    expect($localized->toArray())->toBe([
        'defaultValue' => ['language' => 'en-US', 'value' => 'The Eras Tour'],
    ]);
});

it('adds translated values', function () {
    $localized = LocalizedString::of('Hello')
        ->addTranslation('nl-BE', 'Hallo')
        ->addTranslation('fr-FR', 'Bonjour');

    expect($localized->toArray()['translatedValues'])->toHaveCount(2);
});

it('can use a custom default language', function () {
    $localized = LocalizedString::of('Bonjour', 'fr-FR');

    expect($localized->toArray()['defaultValue']['language'])->toBe('fr-FR');
});
