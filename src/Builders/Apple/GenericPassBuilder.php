<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Validators\GenericApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class GenericPassBuilder extends ApplePassBuilder
{
    protected PassType $type = PassType::Generic;

    protected static function validator(): ApplePassValidator
    {
        return new GenericApplePassValidator;
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
