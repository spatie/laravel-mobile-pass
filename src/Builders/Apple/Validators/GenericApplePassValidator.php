<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

class GenericApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'generic.headerFields' => ['nullable', 'array'],
            'generic.primaryFields' => ['nullable', 'array'],
            'generic.secondaryFields' => ['nullable', 'array'],
            'generic.auxiliaryFields' => ['nullable', 'array'],
            'generic.backFields' => ['nullable', 'array'],
        ]);
    }
}
