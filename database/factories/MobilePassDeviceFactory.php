<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

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
