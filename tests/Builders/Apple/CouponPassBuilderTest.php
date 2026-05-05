<?php

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;

it('compiles back fields into the coupon payload', function () {
    $compiledData = CouponPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addBackField('terms', 'Terms and conditions apply.')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->toHaveKey('coupon');
    expect($compiledData['coupon'])->toHaveKey('backFields');
    expect($compiledData['coupon']['backFields'])->toHaveCount(1);
    expect($compiledData['coupon']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});

it('has a name', function () {
    expect(CouponPassBuilder::name())->toBe('coupon');
});
