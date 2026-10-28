<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

use Illuminate\Validation\Rule;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

abstract class ApplePassValidator
{
    protected function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'sharingProhibited' => ['nullable', 'boolean'],
            'formatVersion' => ['required', 'integer', 'in:1'],
            'organizationName' => ['required', 'string'],
            'passTypeIdentifier' => ['required', 'string'],
            'serialNumber' => ['required', 'string'],
            'webServiceURL' => ['nullable', 'string'],
            'authenticationToken' => ['nullable', 'string', 'min:16'],
            'teamIdentifier' => ['required', 'string'],
            'logoText' => ['nullable', 'string'],
            'suppressStripShine' => ['nullable', 'boolean'],
            'appLaunchURL' => ['nullable', 'string'],
            'associatedStoreIdentifiers' => ['nullable', 'array'],
            'groupingIdentifier' => ['nullable', 'string'],
            'userInfo' => ['nullable', 'array'],
            'barcode' => [],
            'barcodes' => [],
            // Wallet only renders these four formats. The other BarcodeType cases are Google-only.
            'barcodes.*.format' => [Rule::in([
                BarcodeType::Qr->value,
                BarcodeType::Pdf417->value,
                BarcodeType::Aztec->value,
                BarcodeType::Code128->value,
            ])],
            'relevantDate' => [],
            'relevantDates' => [],
            'locations' => [],
            'maxDistance' => [],
            'beacons' => [],
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
