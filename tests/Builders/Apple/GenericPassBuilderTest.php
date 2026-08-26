<?php

use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('compiles back fields into the generic payload', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addBackField('terms', 'Terms and conditions apply.')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->toHaveKey('generic');
    expect($compiledData['generic'])->toHaveKey('backFields');
    expect($compiledData['generic']['backFields'])->toHaveCount(1);
    expect($compiledData['generic']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});

it('bundles an artwork image into the generated pass', function () {
    $generatedPass = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setArtworkImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('artwork.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@2x.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@3x.png'))->toBeTrue();
});

it('registers a remote artwork image', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteArtworkImage('https://example.com/pass/artwork.png')
        ->save();

    expect($pass->images['artwork'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/artwork.png',
            'isRemote' => true,
        ]);
});

it('bundles poster fields into the posterGeneric block', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addPosterHeaderField('event', 'Laracon EU')
        ->addPosterPrimaryField('venue', 'Amsterdam', label: 'Venue Name')
        ->addPosterFooterField('note', 'Doors open at 6pm')
        ->addPosterBackField('terms', 'Terms and conditions apply.')
        ->data();

    expect($compiledData)->toHaveKey('posterGeneric');
    expect($compiledData['posterGeneric'])->toHaveKeys([
        'headerFields',
        'primaryFields',
        'footerFields',
        'backFields',
    ]);
    expect($compiledData['posterGeneric']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
    expect($compiledData['posterGeneric']['primaryFields'][0])->toMatchArray([
        'key' => 'venue',
        'value' => 'Amsterdam',
        'label' => 'Venue Name',
    ]);
    expect($compiledData['posterGeneric']['footerFields'][0])->toMatchArray([
        'key' => 'note',
        'value' => 'Doors open at 6pm',
    ]);
});

it('ships the generic block alongside posterGeneric so older devices keep working', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addHeaderField('event', 'Laracon EU')
        ->addPosterHeaderField('event', 'Laracon EU')
        ->data();

    expect($compiledData['generic']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
    expect($compiledData['posterGeneric']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
});

it('updates matching keys in both the generic and posterGeneric blocks', function () {
    $model = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addAuxiliaryField('seat', '12A')
        ->addPosterPrimaryField('seat', '12A')
        ->save();

    $model->updateField('seat', '13A');

    $hydratedData = $model->builder()->data();

    expect($hydratedData['generic']['auxiliaryFields'][0])->toMatchArray([
        'key' => 'seat',
        'value' => '13A',
    ]);
    expect($hydratedData['posterGeneric']['primaryFields'][0])->toMatchArray([
        'key' => 'seat',
        'value' => '13A',
    ]);
});

it('omits posterGeneric when no poster fields are added', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('posterGeneric');
});

it('round-trips poster fields through save and hydrate', function () {
    $model = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addPosterHeaderField('event', 'Laracon EU')
        ->addPosterBackField('terms', 'Terms and conditions apply.')
        ->save();

    $hydratedData = $model->builder()->data();

    expect($hydratedData['posterGeneric']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
    expect($hydratedData['posterGeneric']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});

it('has a name', function () {
    expect(GenericPassBuilder::name())->toBe('generic');
});
