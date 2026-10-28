<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class GenericObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'classId' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'header' => ['nullable', 'array'],
            'cardTitle' => ['nullable', 'array'],
            'subheader' => ['nullable', 'array'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'notifications' => ['nullable', 'array'],
            'barcode' => ['nullable', 'array'],
        ];
    }
}
