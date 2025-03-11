<?php

namespace Spatie\LaravelMobilePass\Validators;

class StoreCardPassValidator extends PassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'storeCard.headerFields' => ['nullable', 'array'],
            'storeCard.primaryFields' => ['nullable', 'array'],
            'storeCard.secondaryFields' => ['nullable', 'array'],
            'storeCard.auxiliaryFields' => ['nullable', 'array'],
            'storeCard.backFields' => ['nullable', 'array'],
        ]);
    }
}
