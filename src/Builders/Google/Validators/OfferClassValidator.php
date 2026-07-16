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
            'localizedTitle' => ['nullable', 'array'],
            'localizedTitle.defaultValue.value' => ['nullable', 'string'],
            'localizedTitle.defaultValue.language' => ['nullable', 'string'],
            'localizedTitle.translatedValues' => ['nullable', 'array'],
            'localizedTitle.translatedValues.*.language' => ['nullable', 'string'],
            'localizedTitle.translatedValues.*.value' => ['nullable', 'string'],
            'redemptionChannel' => ['nullable', 'string'],
            'provider' => ['nullable', 'string'],
            'localizedProvider' => ['nullable', 'array'],
            'localizedProvider.defaultValue.value' => ['nullable', 'string'],
            'localizedProvider.defaultValue.language' => ['nullable', 'string'],
            'localizedProvider.translatedValues' => ['nullable', 'array'],
            'localizedProvider.translatedValues.*.language' => ['nullable', 'string'],
            'localizedProvider.translatedValues.*.value' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'localizedDetails' => ['nullable', 'array'],
            'localizedDetails.defaultValue.value' => ['nullable', 'string'],
            'localizedDetails.defaultValue.language' => ['nullable', 'string'],
            'localizedDetails.translatedValues' => ['nullable', 'array'],
            'localizedDetails.translatedValues.*.language' => ['nullable', 'string'],
            'localizedDetails.translatedValues.*.value' => ['nullable', 'string'],
            'finePrint' => ['nullable', 'string'],
            'localizedFinePrint' => ['nullable', 'array'],
            'localizedFinePrint.defaultValue.value' => ['nullable', 'string'],
            'localizedFinePrint.defaultValue.language' => ['nullable', 'string'],
            'localizedFinePrint.translatedValues' => ['nullable', 'array'],
            'localizedFinePrint.translatedValues.*.language' => ['nullable', 'string'],
            'localizedFinePrint.translatedValues.*.value' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
