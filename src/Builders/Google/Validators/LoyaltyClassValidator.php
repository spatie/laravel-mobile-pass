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
            'programLogo' => ['nullable', 'array'],
            'rewardsTier' => ['nullable', 'string'],
            'rewardsTierLabel' => ['nullable', 'string'],
            'accountNameLabel' => ['nullable', 'string'],
            'accountIdLabel' => ['nullable', 'string'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
