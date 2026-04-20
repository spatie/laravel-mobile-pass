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
            'subheader' => ['nullable', 'array'],
            'subheader.defaultValue.value' => ['nullable', 'string'],
            'header' => ['nullable', 'array'],
            'header.defaultValue.value' => ['nullable', 'string'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
