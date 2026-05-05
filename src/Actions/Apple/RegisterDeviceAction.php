<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Events\MobilePassAdded;
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

        $registration = $pass->registrations()->firstOrCreate([
            'device_id' => $device->getKey(),
            'pass_type_id' => $passTypeId,
        ]);

        if ($registration->wasRecentlyCreated) {
            event(new MobilePassAdded($pass));
        }

        return $registration;
    }

    protected function mobilePass(string $passSerial): MobilePass
    {
        $mobilePassModel = Config::mobilePassModel();

        return $mobilePassModel::query()
            ->where('pass_serial', $passSerial)
            ->firstOrFail();
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
