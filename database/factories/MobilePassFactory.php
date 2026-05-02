<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

class MobilePassFactory extends Factory
{
    protected $model = MobilePass::class;

    public function definition(): array
    {
        $serialNumber = (string) Str::uuid();

        return [
            'serial_number' => $serialNumber,
            'builder_name' => 'generic',
            'type' => 'generic',
            'platform' => Platform::Apple,
            'images' => [
                'icon' => new Image(getTestSupportPath('images/spatie-thumbnail.png')),
            ],
            'content' => [
                'formatVersion' => 1,
                'organizationName' => 'Laravel King',
                'passTypeIdentifier' => 'pass.app.gowallet',
                'webServiceURL' => '/passkit/',
                'teamIdentifier' => '2SQU7LWHMY',
                'description' => 'Laravel Exclusive Coupon',
                'serialNumber' => $serialNumber,
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

    public function hasRegistrationForDevice(AppleMobilePassDevice $device): static
    {
        return $this->has(
            AppleMobilePassRegistration::factory()->state(['device_id' => $device->getKey()]),
            'registrations'
        );
    }
}
