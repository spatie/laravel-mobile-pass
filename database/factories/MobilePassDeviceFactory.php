<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;

class MobilePassDeviceFactory extends Factory
{
    protected $model = MobilePassDevice::class;

    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'push_token' => fake()->uuid(),
        ];
    }
}
