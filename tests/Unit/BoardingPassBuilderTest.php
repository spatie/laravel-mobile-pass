<?php

use Spatie\LaravelMobilePass\Builders\BoardingPasses\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Entities\Seat;

use Spatie\LaravelMobilePass\Support\PkPass;
use function Pest\testDirectory;

it('builds a basic boarding pass', function () {
    $pass = AirlinePassBuilder::make()
        ->setOrganisationName('My organisation')
        ->setSerialNumber(rand(1, 100) * 100)
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
                x1Path: testDirectory('Helpers/Images/spatie-thumbnail.png')
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
        ))

        ->generate();

    // For the moment, we'll just store a file here
    // and manually open it to check it works.
    file_put_contents('test.pkpass', $pass);

    $pkPass = PkPass::loadFromString($pass);

c    dd($pkPass->pass());
});
