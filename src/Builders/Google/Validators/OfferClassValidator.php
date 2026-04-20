<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class OfferClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'title' => ['required', 'string'],
            'redemptionChannel' => ['nullable', 'string'],
            'provider' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'finePrint' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
