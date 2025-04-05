<?php

use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

it('builds a basic airline boarding pass', function () {
    $airlinePassBuilder = AirlinePassBuilder::make()
        ->setOrganisationName('My organisation')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
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
        ->setAuxiliaryFields(
            FieldContent::make('departs')
                ->withLabel('Departs')
                ->withValue(now()->toIso8601String()),
            FieldContent::make('class')
                ->withLabel('Class')
                ->withValue('Economy'),
        )
        ->setIconImage(
            Image::make(
                x1Path: getTestSupportPath('images/spatie-thumbnail.png')
            )
        )

        // Now set the semantic fields.
        ->setDepartureAirportCode('AUH')
        ->setDepartureAirportName('Abu Dhabi Intl')
        ->setDepartureLocationDescription('Abu Dhabi Intl')
        ->setDestinationAirportCode('LHR')
        ->setDestinationAirportName('London Heathrow')
        ->setDestinationLocationDescription('Abu Dhabi Intl')
        ->setSeats(Seat::make(
            number: '66F',
        ));

    $generatedPass = $airlinePassBuilder->generate();

    expect($generatedPass)->toMatchMobilePassSnapshot();

    // first save, model gets created
    $mobilePass = $airlinePassBuilder->save();

    // second save, model gets updated
    $mobilePass
        ->airlinePassBuilder()
        ->setSeats(Seat::make(
            number: '123DAN',
        ))->save();

    expect($mobilePass->generate())->toMatchMobilePassSnapshot();
});

it('has a name', function () {
    expect(AirlinePassBuilder::name())->toBe('airline');
});
