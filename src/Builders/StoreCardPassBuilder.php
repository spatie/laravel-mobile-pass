<?php

namespace Spatie\LaravelMobilePass\Builders;

use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Validators\PassValidator;
use Spatie\LaravelMobilePass\Validators\StoreCardPassValidator;

class StoreCardPassBuilder extends PassBuilder
{
    protected PassType $type = PassType::StoreCard;

    protected static function validator(): PassValidator
    {
        return new StoreCardPassValidator;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'storeCard' => array_filter([
                    'primaryFields' => $this->primaryFields?->toArray(),
                    'secondaryFields' => $this->secondaryFields?->toArray(),
                    'headerFields' => $this->headerFields?->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->toArray(),
                ]),
            ],
        );
    }
}
