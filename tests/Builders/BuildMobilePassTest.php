<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Illuminate\Validation\ValidationException;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

it('can create a mobile pass', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganisationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber(123456)
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->addField('departure', 'ABU', label: 'Abu Dhabi International')
        ->addField('destination', 'LHR', label: 'London Heathrow')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addSecondaryField('gate', 'D68')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'));

    $pass->save();

    $passkeyContent = $pass->generate();

    expect($passkeyContent)->toMatchMobilePassSnapshot();
});

it('throws a validation exception when a required field is missing', function () {
    GenericPassBuilder::make()
        ->setOrganisationName('Test Org')
        ->setSerialNumber(123456)
        // description intentionally omitted
        ->data();
})->throws(ValidationException::class);

it('updates a field', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganisationName('My organisation')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->save();

    $pass->updateField('flight-no', 'UPDATED');

    expect($pass->generate())->toMatchMobilePassSnapshot();
});
