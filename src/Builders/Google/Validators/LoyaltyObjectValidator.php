<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class LoyaltyObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'classId' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'accountId' => ['nullable', 'string'],
            'accountName' => ['nullable', 'string'],
            'loyaltyPoints' => ['nullable', 'array'],
            'barcode' => ['nullable', 'array'],
        ];
    }
}
