<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

it('builds a basic event ticket', function () {
    $eventTicketPassBuilder = EventTicketPassBuilder::make()
        ->setOrganisationName('My organisation')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addHeaderField('event', 'Laracon EU')
        ->addPrimaryField('venue', 'Amsterdam')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addAuxiliaryField('seat', 'A12')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'));

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
