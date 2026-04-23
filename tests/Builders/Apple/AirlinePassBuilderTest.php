<?php

use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

it('builds a basic airline boarding pass', function () {
    $airlinePassBuilder = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->addField('departure', 'ABU', label: 'Abu Dhabi International')
        ->addField('destination', 'LHR', label: 'London Heathrow')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addSecondaryField('gate', 'D68')
        ->addAuxiliaryField('departs', now()->toIso8601String())
        ->addAuxiliaryField('class', 'Economy')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))

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
    /** @var AirlinePassBuilder $rebuilder */
    $rebuilder = $mobilePass->builder();

    $rebuilder
        ->setSeats(Seat::make(number: '123DAN'))
        ->save();

    expect($mobilePass->generate())->toMatchMobilePassSnapshot();
});

it('has a name', function () {
    expect(AirlinePassBuilder::name())->toBe('airline');
});
