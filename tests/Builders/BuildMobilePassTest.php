<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

it('can create a mobile pass', function () {
    $pass = GenericPassBuilder::make()
        ->setDescription('Hello!')
        ->setSerialNumber(123456)

        ->setHeaderFields(
            FieldContent::make('flight-no')
                ->withLabel('Flight')
                ->withValue('EY066'),
            FieldContent::make('seat')
                ->withLabel('Seat')
                ->withValue('66F')
        )
        ->setPrimaryFields(
            FieldContent::make('departure')
                ->withLabel('Abu Dhabi International')
                ->withValue('ABU'),
            FieldContent::make('destination')
                ->withLabel('London Heathrow')
                ->withValue('LHR'),
        )
        ->setSecondaryFields(
            FieldContent::make('name')
                ->withLabel('Name')
                ->withValue('Dan Johnson'),
            FieldContent::make('gate')
                ->withLabel('Gate')
                ->withValue('D68')
        )

        ->setIconImage(
            Image::make(
                x1Path: getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

    $pass->save();

    $passkeyContent = $pass->generate();

    expect($passkeyContent)->toMatchMobilePassSnapshot();
});

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
        ->setHeaderFields(
            FieldContent::make('flight-no')
                ->withLabel('Flight')
                ->withValue('EY066'),
            FieldContent::make('seat')
                ->withLabel('Seat')
                ->withValue('66F')
        )
        ->save();

    // We should be able to update a field
    $pass
        ->builder()
        ->updateField('flight-no', fn (FieldContent $field) => $field->withValue('UPDATED')
        )
        ->save();

    expect($pass->generate())->toMatchMobilePassSnapshot();
});
