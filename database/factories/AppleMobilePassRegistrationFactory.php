<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

class AppleMobilePassRegistrationFactory extends Factory
{
    protected $model = AppleMobilePassRegistration::class;

    public function definition(): array
    {
        return [
            'device_id' => AppleMobilePassDevice::factory(),
            'pass_type_id' => 'pass.com.example',
            'pass_serial' => MobilePass::factory(),
        ];
    }
}
