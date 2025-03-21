<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class MobilePassRegistrationFactory extends Factory
{
    protected $model = MobilePassRegistration::class;

    public function definition()
    {
        return [
            'device_id' => MobilePassDevice::factory(),
            'pass_type_id' => 'pass.com.example',
            'pass_serial' => MobilePass::factory(),
        ];
    }
}
