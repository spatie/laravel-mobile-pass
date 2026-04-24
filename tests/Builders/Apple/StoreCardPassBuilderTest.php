<?php

use Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder;

it('compiles back fields into the store card payload', function () {
    $compiledData = StoreCardPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addBackField('terms', 'Terms and conditions apply.')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->toHaveKey('storeCard');
    expect($compiledData['storeCard'])->toHaveKey('backFields');
    expect($compiledData['storeCard']['backFields'])->toHaveCount(1);
    expect($compiledData['storeCard']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});

it('has a name', function () {
    expect(StoreCardPassBuilder::name())->toBe('store_card');
});
