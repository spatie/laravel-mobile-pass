<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Builders\Apple\TrainPassBuilder;

it('builds a basic train pass', function () {
    $trainPassBuilder = TrainPassBuilder::make()
        ->setOrganizationName('SNCB')
        ->setSerialNumber(123456)
        ->setDescription('Brussels to Antwerp, coach 3')
        ->addHeaderField('departure', 'BRU', label: 'Brussels-Central')
        ->addHeaderField('destination', 'ANT', label: 'Antwerp-Central')
        ->addSecondaryField('name', 'George Harrison')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setPassengerName(PersonName::make(givenName: 'George', familyName: 'Harrison'))
        ->setCarNumber('3')
        ->setDeparturePlatform('A')
        ->setDepartureStationName('Brussels-Central')
        ->setDestinationPlatform('2')
        ->setDestinationStationName('Antwerp-Central')
        ->setSeats(Seat::make(number: '24B'));

    $generatedPass = $trainPassBuilder->generate();

    expect($generatedPass)->toMatchMobilePassSnapshot();
});

it('compiles rail semantics into the semantics payload', function () {
    $compiledData = builderWithEveryRailDetail()->data();

    expect($compiledData['semantics'])->toMatchArray(expectedRailSemantics());
});

it('omits rail semantics when none are set', function () {
    $compiledData = TrainPassBuilder::make()
        ->setOrganizationName('SNCB')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('semantics');
});

it('round-trips rail semantics through save and hydrate', function () {
    $model = builderWithEveryRailDetail()->save();

    expect($model->builder()->data()['semantics'])->toMatchArray(expectedRailSemantics());
});

it('has a name', function () {
    expect(TrainPassBuilder::name())->toBe('train');
});

function builderWithEveryRailDetail(): TrainPassBuilder
{
    return TrainPassBuilder::make()
        ->setOrganizationName('SNCB')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setCarNumber('3')
        ->setDeparturePlatform('A')
        ->setDepartureStationName('Brussels-Central')
        ->setDestinationPlatform('2')
        ->setDestinationStationName('Antwerp-Central');
}

function expectedRailSemantics(): array
{
    return [
        'carNumber' => '3',
        'departurePlatform' => 'A',
        'departureStationName' => 'Brussels-Central',
        'destinationPlatform' => '2',
        'destinationStationName' => 'Antwerp-Central',
    ];
}
