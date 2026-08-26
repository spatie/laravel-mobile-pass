<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;

it('serializes every property, including the name suffix', function () {
    $personName = PersonName::make(
        familyName: 'Harrison',
        givenName: 'George',
        middleName: 'Harold',
        namePrefix: 'Mr.',
        nameSuffix: 'Jr.',
        nickname: 'The Quiet One',
        phoneticRepresentation: 'jɔːrdʒ ˈhærɪsən',
    );

    expect($personName->toArray())->toBe([
        'familyName' => 'Harrison',
        'givenName' => 'George',
        'middleName' => 'Harold',
        'namePrefix' => 'Mr.',
        'nameSuffix' => 'Jr.',
        'nickname' => 'The Quiet One',
        'phoneticRepresentation' => 'jɔːrdʒ ˈhærɪsən',
    ]);
});

it('hydrates the name suffix from an array', function () {
    $personName = PersonName::fromArray([
        'nameSuffix' => 'Jr.',
    ]);

    expect($personName->nameSuffix)->toBe('Jr.');
    expect($personName->toArray())->toBe(['nameSuffix' => 'Jr.']);
});
