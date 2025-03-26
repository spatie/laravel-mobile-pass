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
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                ]),
            ],
        );
    }
}
