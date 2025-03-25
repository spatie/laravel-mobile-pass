<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Builders\GenericPassBuilder;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;

class MobilePassFactory extends Factory
{
    protected $model = MobilePass::class;

    public function definition()
    {
        return [
            'builder_class' => GenericPassBuilder::class,
            'images' => [
                'icon' => Image::make(
                    getTestSupportPath('images/spatie-thumbnail.png')
                )
            ],
            'content' => [
                'formatVersion' => 1,
                'organizationName' => 'Laravel King',
                'passTypeIdentifier' => 'pass.app.gowallet',
                'webServiceURL' => '/passkit/',
                'teamIdentifier' => '2SQU7LWHMY',
                'description' => 'Laravel Exclusive Coupon',
                'serialNumber' => '0195cd4a-9f78-717f-b397-59cad6b78a27',
                'backgroundColor' => 'rgb(81, 35, 20)',
                'foregroundColor' => 'rgb(255, 134, 41)',
                'labelColor' => 'rgb(245, 235, 220)',
                'passType' => 'coupon',
                'coupon' => [
                    'transitType' => 'PKTransitTypeAir',
                    'headerFields' => [
                        'key' => 'expiry',
                        'label' => 'Expires',
                        'value' => '2025-01-02T00:00:00+00:00',
                        'dateStyle' => 'PKDateStyleShort',
                        'isRelative' => true,
                    ],
                ],
            ],
        ];
    }

    // public function configure(): static
    // {
    //     return $this->afterMaking(function (MobilePass $mobilePass) {
    //         $mobilePass
    //             ->builder()
    //             ->setIconImage(
    //                 Image::make(
    //                     getTestSupportPath('images/spatie-thumbnail.png')
    //                 )
    //             );
    //     });
    // }

    public function hasRegistrationForDevice(MobilePassDevice $device): static
    {
        return $this->hasRegistrations(1, ['device_id' => $device->getKey()]);
    }
}
