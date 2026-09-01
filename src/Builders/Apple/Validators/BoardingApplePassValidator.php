<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

use Illuminate\Validation\Rule;
use Spatie\LaravelMobilePass\Enums\TransitType;

class BoardingApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'boardingPass.transitType' => [
                'required',
                Rule::enum(TransitType::class),
            ],
            'boardingPass.headerFields' => ['nullable', 'array'],
            'boardingPass.primaryFields' => ['nullable', 'array'],
            'boardingPass.secondaryFields' => ['nullable', 'array'],
            'boardingPass.auxiliaryFields' => ['nullable', 'array'],
            'boardingPass.backFields' => ['nullable', 'array'],
            'changeSeatURL' => ['nullable', 'string'],
            'entertainmentURL' => ['nullable', 'string'],
            'purchaseAdditionalBaggageURL' => ['nullable', 'string'],
            'purchaseLoungeAccessURL' => ['nullable', 'string'],
            'purchaseWifiURL' => ['nullable', 'string'],
            'upgradeURL' => ['nullable', 'string'],
            'managementURL' => ['nullable', 'string'],
            'registerServiceAnimalURL' => ['nullable', 'string'],
            'reportLostBagURL' => ['nullable', 'string'],
            'requestWheelchairURL' => ['nullable', 'string'],
            'trackBagsURL' => ['nullable', 'string'],
            'transitProviderEmail' => ['nullable', 'string'],
            'transitProviderPhoneNumber' => ['nullable', 'string'],
            'transitProviderWebsiteURL' => ['nullable', 'string'],
        ]);
    }
}
