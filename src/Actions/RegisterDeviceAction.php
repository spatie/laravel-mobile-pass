<?php

namespace Spatie\LaravelMobilePass\Actions;

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Support\Config;

class RegisterDeviceAction
{
    public function execute(
        string $deviceId,
        string $pushToken,
        string $passTypeId,
        string $passSerial,
    ) {
        $pass = $this->mobilePass($passSerial);

        $device = $this->device($deviceId, $pushToken);

        $registrationProperties = $this->registrationProperties(
            $device, $passTypeId, $passSerial
        );

        return $pass->registrations()->firstOrCreate($registrationProperties);
    }

    protected function mobilePass(string $passSerial): MobilePass
    {
        $mobilePassModel = Config::mobilePassModel();

        return $mobilePassModel::findOrFail($passSerial);
    }

    protected function device(string $deviceId, string $pushToken): MobilePassDevice
    {
        $mobilePassDeviceModel = Config::deviceModel();

        return $mobilePassDeviceModel::updateOrCreate(
            ['id' => $deviceId],
            ['push_token' => $pushToken],
        );
    }

    protected function registrationProperties(MobilePassDevice $device, string $passTypeId, string $passSerial): array
    {
        return [
            'device_id' => $device->getKey(),
            'pass_type_id' => $passTypeId,
            'pass_serial' => $passSerial,
        ];
    }
}
