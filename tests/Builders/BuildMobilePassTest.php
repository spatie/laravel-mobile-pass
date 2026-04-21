<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Illuminate\Validation\ValidationException;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

it('can create a mobile pass', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganisationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber(123456)
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->addPrimaryField('departure', 'ABU', label: 'Abu Dhabi International')
        ->addPrimaryField('destination', 'LHR', label: 'London Heathrow')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addSecondaryField('gate', 'D68')
        ->setIconImage(
            Image::make(
                x1Path: getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

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
        ->setIconImage(
            Image::make(
                x1Path: getTestSupportPath('images/spatie-thumbnail.png')
            )
        )
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->save();

    $pass
        ->builder()
        ->updateField('flight-no', 'UPDATED')
        ->save();

    expect($pass->generate())->toMatchMobilePassSnapshot();
});
