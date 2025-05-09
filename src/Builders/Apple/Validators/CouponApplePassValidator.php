<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

class CouponApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'coupon.headerFields' => ['nullable', 'array'],
            'coupon.primaryFields' => ['nullable', 'array'],
            'coupon.secondaryFields' => ['nullable', 'array'],
            'coupon.auxiliaryFields' => ['nullable', 'array'],
            'coupon.backFields' => ['nullable', 'array'],
        ]);
    }
}
