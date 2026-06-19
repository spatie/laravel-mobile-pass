<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('builds a basic event ticket', function () {
    $eventTicketPassBuilder = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addHeaderField('event', 'Laracon EU')
        ->addField('venue', 'Amsterdam')
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

it('bundles a background image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setBackgroundImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('background.png'))->toBeTrue()
        ->and($reader->containsFile('background@2x.png'))->toBeTrue()
        ->and($reader->containsFile('background@3x.png'))->toBeTrue();
});

it('registers a remote background image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteBackgroundImage('https://example.com/pass/background.png')
        ->save();

    expect($pass->images['background'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/background.png',
            'isRemote' => true,
        ]);
});

it('has a name', function () {
    expect(EventTicketPassBuilder::name())->toBe('event_ticket');
});
