<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class LoyaltyClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'programName' => ['required', 'array'],
            'programName.defaultValue.value' => ['required', 'string'],
            'programName.defaultValue.language' => ['required', 'string'],
            'programName.translatedValues' => ['nullable', 'array'],
            'programName.translatedValues.*.language' => ['nullable', 'string'],
            'programName.translatedValues.*.value' => ['nullable', 'string'],
            'programLogo' => ['nullable', 'array'],
            'rewardsTier' => ['nullable', 'array'],
            'rewardsTier.defaultValue.value' => ['nullable', 'string'],
            'rewardsTier.defaultValue.language' => ['nullable', 'string'],
            'rewardsTier.translatedValues' => ['nullable', 'array'],
            'rewardsTier.translatedValues.*.language' => ['nullable', 'string'],
            'rewardsTier.translatedValues.*.value' => ['nullable', 'string'],
            'rewardsTierLabel' => ['nullable', 'array'],
            'rewardsTierLabel.defaultValue.value' => ['nullable', 'string'],
            'rewardsTierLabel.defaultValue.language' => ['nullable', 'string'],
            'rewardsTierLabel.translatedValues' => ['nullable', 'array'],
            'rewardsTierLabel.translatedValues.*.language' => ['nullable', 'string'],
            'rewardsTierLabel.translatedValues.*.value' => ['nullable', 'string'],
            'accountNameLabel' => ['nullable', 'array'],
            'accountNameLabel.defaultValue.value' => ['nullable', 'string'],
            'accountNameLabel.defaultValue.language' => ['nullable', 'string'],
            'accountNameLabel.translatedValues' => ['nullable', 'array'],
            'accountNameLabel.translatedValues.*.language' => ['nullable', 'string'],
            'accountNameLabel.translatedValues.*.value' => ['nullable', 'string'],
            'accountIdLabel' => ['nullable', 'array'],
            'accountIdLabel.defaultValue.value' => ['nullable', 'string'],
            'accountIdLabel.defaultValue.language' => ['nullable', 'string'],
            'accountIdLabel.translatedValues' => ['nullable', 'array'],
            'accountIdLabel.translatedValues.*.language' => ['nullable', 'string'],
            'accountIdLabel.translatedValues.*.value' => ['nullable', 'string'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
