<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Illuminate\Validation\ValidationException;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Enums\DateType;
use Spatie\LaravelMobilePass\Enums\TimeStyleType;
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

it('can create a mobile pass', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber(123456)
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->addField('departure', 'ABU', label: 'Abu Dhabi International')
        ->addField('destination', 'LHR', label: 'London Heathrow')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addSecondaryField('gate', 'D68')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'));

    $pass->save();

    $passkeyContent = $pass->generate();

    expect($passkeyContent)->toMatchMobilePassSnapshot();
});

it('throws InvalidPass when a required field is missing', function () {
    GenericPassBuilder::make()
        ->setOrganizationName('Test Org')
        ->setSerialNumber(123456)
        // description intentionally omitted
        ->data();
})->throws(InvalidPass::class);

it('InvalidPass is also catchable as ValidationException', function () {
    try {
        GenericPassBuilder::make()
            ->setOrganizationName('Test Org')
            ->setSerialNumber(123456)
            ->data();

        $this->fail('Expected an exception to be thrown.');
    } catch (ValidationException $exception) {
        expect($exception)->toBeInstanceOf(InvalidPass::class);
        expect($exception->errors())->toHaveKey('description');
    }
});

it('updates a field', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->save();

    $pass->updateField('flight-no', 'UPDATED');

    expect($pass->generate())->toMatchMobilePassSnapshot();
});

it('keeps the serial number stable when re-hydrated', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber('stable-serial-123')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->save();

    expect($pass->content['serialNumber'])->toBe('stable-serial-123');

    expect($pass->builder()->data()['serialNumber'])->toBe('stable-serial-123');

    $pass->updateField('flight-no', 'UPDATED');
    $pass->refresh();
    expect($pass->content['serialNumber'])->toBe('stable-serial-123');
});

it('keeps the data stable when re-hydrated', function () {
    $passBuilder = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setDescription('Hello!')
        ->setSerialNumber('stable-serial-123')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addField('date', now()->toIso8601String(), dateStyle: DateType::Medium)
        ->addField('time', now()->toIso8601String(), timeStyle: TimeStyleType::Short);

    $model = $passBuilder->save();

    $builderData = $passBuilder->data();
    $modelData = $model->builder()->data();

    expect($modelData)->toBe($builderData);

    $builderGenerated = $passBuilder->generate();
    $modelGenerated = $model->generate();

    expect($modelGenerated)->toBe($builderGenerated);
});
