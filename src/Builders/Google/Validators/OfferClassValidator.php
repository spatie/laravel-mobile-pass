<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class OfferClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'title' => ['required', 'array'],
            'title.defaultValue.value' => ['required', 'string'],
            'title.defaultValue.language' => ['required', 'string'],
            'title.translatedValues' => ['nullable', 'array'],
            'title.translatedValues.*.language' => ['nullable', 'string'],
            'title.translatedValues.*.value' => ['nullable', 'string'],
            'redemptionChannel' => ['nullable', 'string'],
            'provider' => ['nullable', 'array'],
            'provider.defaultValue.value' => ['nullable', 'string'],
            'provider.defaultValue.language' => ['nullable', 'string'],
            'provider.translatedValues' => ['nullable', 'array'],
            'provider.translatedValues.*.language' => ['nullable', 'string'],
            'provider.translatedValues.*.value' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'details.defaultValue.value' => ['nullable', 'string'],
            'details.defaultValue.language' => ['nullable', 'string'],
            'details.translatedValues' => ['nullable', 'array'],
            'details.translatedValues.*.language' => ['nullable', 'string'],
            'details.translatedValues.*.value' => ['nullable', 'string'],
            'finePrint' => ['nullable', 'array'],
            'finePrint.defaultValue.value' => ['nullable', 'string'],
            'finePrint.defaultValue.language' => ['nullable', 'string'],
            'finePrint.translatedValues' => ['nullable', 'array'],
            'finePrint.translatedValues.*.language' => ['nullable', 'string'],
            'finePrint.translatedValues.*.value' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
