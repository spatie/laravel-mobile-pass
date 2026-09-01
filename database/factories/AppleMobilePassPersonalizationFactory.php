<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassPersonalization;
use Spatie\LaravelMobilePass\Models\MobilePass;

class AppleMobilePassPersonalizationFactory extends Factory
{
    protected $model = AppleMobilePassPersonalization::class;

    public function definition(): array
    {
        return [
            'mobile_pass_id' => MobilePass::factory(),
            'description' => 'Sign up to earn points.',
            'required_fields' => ['PKPassPersonalizationFieldName', 'PKPassPersonalizationFieldEmailAddress'],
            'terms_and_conditions' => null,
            'personalization_token' => null,
            'submitted_info' => null,
            'personalized_at' => null,
        ];
    }
}
