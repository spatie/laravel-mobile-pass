<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Validators\CouponPassValidator;
use Spatie\LaravelMobilePass\Validators\PassValidator;

class CouponPassBuilder extends PassBuilder
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
