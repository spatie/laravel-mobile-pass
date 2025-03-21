<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class MobilePassFactory extends Factory
{
    protected $model = MobilePass::class;

    public function definition()
    {
        return [

        ];
    }

    public function withIconImage(): static
    {
        return $this->afterMaking(function (MobilePass $mobilePass) {
            $mobilePass->setIconImage(
                Image::make(
                    getTestSupportPath('images/spatie-thumbnail.png')
                )
            );
        });
    }

    public function hasRegistrationForDevice(MobilePassDevice $device): static
    {
        return $this->hasRegistrations(1, ['device_id' => $device->getKey()]);
    }
}
