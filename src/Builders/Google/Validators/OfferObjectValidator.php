<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class OfferObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'classId' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'redemptionCode' => ['nullable', 'string'],
            'barcode' => ['nullable', 'array'],
        ];
    }
}
