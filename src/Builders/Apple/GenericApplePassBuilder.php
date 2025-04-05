<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Validators\GenericPassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\PassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class GenericApplePassBuilder extends ApplePassBuilder
{
    protected PassType $type = PassType::Generic;

    protected static function validator(): PassValidator
    {
        return new GenericPassValidator;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'generic' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                ]),
            ],
        );
    }
}
