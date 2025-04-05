<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Validators\CouponPassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\PassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class CouponApplePassBuilder extends ApplePassBuilder
{
    protected PassType $type = PassType::Coupon;

    protected static function validator(): PassValidator
    {
        return new CouponPassValidator;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'coupon' => array_filter([
                    'primaryFields' => $this->primaryFields?->toArray(),
                    'secondaryFields' => $this->secondaryFields?->toArray(),
                    'headerFields' => $this->headerFields?->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->toArray(),
                ]),
            ],
        );
    }
}
