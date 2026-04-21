<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class RegisterDeviceAction
{
    public function execute(
        string $deviceId,
        string $pushToken,
        string $passTypeId,
        string $passSerial,
    ): AppleMobilePassRegistration {
        $pass = $this->mobilePass($passSerial);
        $device = $this->device($deviceId, $pushToken);

        return $pass->registrations()->firstOrCreate([
            'device_id' => $device->getKey(),
            'pass_type_id' => $passTypeId,
            'pass_serial' => $passSerial,
        ]);
    }

    protected function mobilePass(string $passSerial): MobilePass
    {
        $mobilePassModel = Config::mobilePassModel();

        return $mobilePassModel::query()->findOrFail($passSerial);
    }

    protected function device(string $deviceId, string $pushToken): AppleMobilePassDevice
    {
        $mobilePassDeviceModel = Config::appleDeviceModel();

        return $mobilePassDeviceModel::query()->updateOrCreate(
            ['id' => $deviceId],
            ['push_token' => $pushToken],
        );
    }
}
