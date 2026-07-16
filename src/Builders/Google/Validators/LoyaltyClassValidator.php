<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class LoyaltyClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'programName' => ['required', 'string'],
            'localizedProgramName' => ['nullable', 'array'],
            'localizedProgramName.defaultValue.value' => ['nullable', 'string'],
            'localizedProgramName.defaultValue.language' => ['nullable', 'string'],
            'localizedProgramName.translatedValues' => ['nullable', 'array'],
            'localizedProgramName.translatedValues.*.language' => ['nullable', 'string'],
            'localizedProgramName.translatedValues.*.value' => ['nullable', 'string'],
            'programLogo' => ['nullable', 'array'],
            'rewardsTier' => ['nullable', 'string'],
            'localizedRewardsTier' => ['nullable', 'array'],
            'localizedRewardsTier.defaultValue.value' => ['nullable', 'string'],
            'localizedRewardsTier.defaultValue.language' => ['nullable', 'string'],
            'localizedRewardsTier.translatedValues' => ['nullable', 'array'],
            'localizedRewardsTier.translatedValues.*.language' => ['nullable', 'string'],
            'localizedRewardsTier.translatedValues.*.value' => ['nullable', 'string'],
            'rewardsTierLabel' => ['nullable', 'string'],
            'localizedRewardsTierLabel' => ['nullable', 'array'],
            'localizedRewardsTierLabel.defaultValue.value' => ['nullable', 'string'],
            'localizedRewardsTierLabel.defaultValue.language' => ['nullable', 'string'],
            'localizedRewardsTierLabel.translatedValues' => ['nullable', 'array'],
            'localizedRewardsTierLabel.translatedValues.*.language' => ['nullable', 'string'],
            'localizedRewardsTierLabel.translatedValues.*.value' => ['nullable', 'string'],
            'accountNameLabel' => ['nullable', 'string'],
            'localizedAccountNameLabel' => ['nullable', 'array'],
            'localizedAccountNameLabel.defaultValue.value' => ['nullable', 'string'],
            'localizedAccountNameLabel.defaultValue.language' => ['nullable', 'string'],
            'localizedAccountNameLabel.translatedValues' => ['nullable', 'array'],
            'localizedAccountNameLabel.translatedValues.*.language' => ['nullable', 'string'],
            'localizedAccountNameLabel.translatedValues.*.value' => ['nullable', 'string'],
            'accountIdLabel' => ['nullable', 'string'],
            'localizedAccountIdLabel' => ['nullable', 'array'],
            'localizedAccountIdLabel.defaultValue.value' => ['nullable', 'string'],
            'localizedAccountIdLabel.defaultValue.language' => ['nullable', 'string'],
            'localizedAccountIdLabel.translatedValues' => ['nullable', 'array'],
            'localizedAccountIdLabel.translatedValues.*.language' => ['nullable', 'string'],
            'localizedAccountIdLabel.translatedValues.*.value' => ['nullable', 'string'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
