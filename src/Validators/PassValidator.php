<?php

namespace Spatie\LaravelMobilePass\Validators;

use Illuminate\Validation\Validator;

abstract class PassValidator
{
    protected function rules(): array
    {
        return [
            // formatVersion must be '1'
            'description' => ['string'],
            'formatVersion' => ['required', 'integer', 'in:1'],
            'organizationName' => ['required', 'string'],
            'passTypeIdentifier' => ['required', 'string'],
            'serialNumber' => ['required', 'string'],
            'webServiceURL' => ['nullable', 'string'], // TODO: required?
            'authenticationToken' => ['nullable', 'string', 'min:16'], // TODO: required with webServiceURL?
            'teamIdentifier' => ['required', 'string'],
            'logoText' => ['nullable', 'string'],
            'barcode' => [],
            'foregroundColor' => [],
            'backgroundColor' => [],
            'labelColor' => [],
            'icon' => [],
        ];
    }

    public function validate(array $compiledData): array
    {
        return validator($compiledData)->validate();
    }
}
