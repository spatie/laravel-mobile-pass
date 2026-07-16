<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class GenericClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'cardTitle' => ['nullable', 'array'],
            'cardTitle.defaultValue.value' => ['nullable', 'string'],
            'cardTitle.defaultValue.language' => ['nullable', 'string'],
            'cardTitle.translatedValues' => ['nullable', 'array'],
            'cardTitle.translatedValues.*.language' => ['nullable', 'string'],
            'cardTitle.translatedValues.*.value' => ['nullable', 'string'],
            'subheader' => ['nullable', 'array'],
            'subheader.defaultValue.value' => ['nullable', 'string'],
            'subheader.defaultValue.language' => ['nullable', 'string'],
            'subheader.translatedValues' => ['nullable', 'array'],
            'subheader.translatedValues.*.language' => ['nullable', 'string'],
            'subheader.translatedValues.*.value' => ['nullable', 'string'],
            'header' => ['nullable', 'array'],
            'header.defaultValue.value' => ['nullable', 'string'],
            'header.defaultValue.language' => ['nullable', 'string'],
            'header.translatedValues' => ['nullable', 'array'],
            'header.translatedValues.*.language' => ['nullable', 'string'],
            'header.translatedValues.*.value' => ['nullable', 'string'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
