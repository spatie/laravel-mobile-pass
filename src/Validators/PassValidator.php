<?php

namespace Spatie\LaravelMobilePass\Validators;

abstract class PassValidator
{
    protected function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'formatVersion' => ['required', 'integer', 'in:1'],
            'organizationName' => ['required', 'string'],
            'passTypeIdentifier' => ['required', 'string'],
            'serialNumber' => ['required', 'string'],
            'webServiceURL' => ['nullable', 'string'],
            'authenticationToken' => ['nullable', 'string', 'min:16'],
            'teamIdentifier' => ['required', 'string'],
            'logoText' => ['nullable', 'string'],
            'barcode' => [],
            'semantics' => [],
            'primaryFields' => [],

            'foregroundColor' => [],
            'backgroundColor' => [],
            'labelColor' => [],

            'iconImagePath' => [],
            'icon@2xImagePath' => [],
            'icon@3xImagePath' => [],
            'logoImagePath' => [],
            'logo@2xImagePath' => [],
            'logo@3xImagePath' => [],
        ];
    }

    public function validate(array $compiledData): array
{   
        return validator($compiledData, $this->rules())->validate();
    }
}
