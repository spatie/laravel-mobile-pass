<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\CouponApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class CouponPassBuilder extends ApplePassBuilder
{
    protected PassType $type = PassType::Coupon;

    protected static function validator(): ApplePassValidator
    {
        return new CouponApplePassValidator;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'coupon' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                ]),
            ],
        );
    }
}
