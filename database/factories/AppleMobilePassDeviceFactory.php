<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;

class AppleMobilePassDeviceFactory extends Factory
{
    protected $model = AppleMobilePassDevice::class;

    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'push_token' => fake()->uuid(),
        ];
    }
}
