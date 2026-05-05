<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

abstract class ApplePassValidator
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
            'barcodes' => [],
            'relevantDate' => [],
            'locations' => [],
            'maxDistance' => [],
            'nfc' => [],
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
        $validator = validator($compiledData, $this->rules());

        if ($validator->fails()) {
            throw new InvalidPass($validator);
        }

        return $validator->validated();
    }
}
