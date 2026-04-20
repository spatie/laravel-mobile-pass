<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

it('builds a basic event ticket', function () {
    $eventTicketPassBuilder = EventTicketPassBuilder::make()
        ->setOrganisationName('My organisation')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setHeaderFields(
            FieldContent::make('event')
                ->withLabel('Event')
                ->withValue('Laracon EU')
        )
        ->setPrimaryFields(
            FieldContent::make('venue')
                ->withLabel('Venue')
                ->withValue('Amsterdam'),
        )
        ->setSecondaryFields(
            FieldContent::make('name')
                ->withLabel('Name')
                ->withValue('Dan Johnson'),
        )
        ->setAuxiliaryFields(
            FieldContent::make('seat')
                ->withLabel('Seat')
                ->withValue('A12'),
        )
        ->setIconImage(
            Image::make(
                x1Path: getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

    $compiledData = $eventTicketPassBuilder->data();

    expect($compiledData)->toHaveKey('eventTicket');
    expect($compiledData['eventTicket'])->toHaveKeys([
        'primaryFields',
        'secondaryFields',
        'headerFields',
        'auxiliaryFields',
    ]);

    $generatedPass = $eventTicketPassBuilder->generate();

    expect($generatedPass)->toMatchMobilePassSnapshot();
});

it('has a name', function () {
    expect(EventTicketPassBuilder::name())->toBe('event_ticket');
});
