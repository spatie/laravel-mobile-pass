<?php

use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

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

it('has a name', function () {
    expect(GenericPassBuilder::name())->toBe('generic');
});
