<?php

namespace Spatie\LaravelMobilePass\Actions;

use Spatie\LaravelMobilePass\Models\MobilePassRegistration;
use Spatie\LaravelMobilePass\Support\Config;

class UnregisterDeviceAction
{
    public function execute(string $deviceId, string $passSerial)
    {
        $mobilePassRegistrationModel = Config::mobilePassRegistrationModel();

        $mobilePassRegistrationModel::query()
            ->where([
                'device_id' => $deviceId,
                'pass_serial' => $passSerial,
            ])->each(fn(MobilePassRegistration $registration) => $registration->delete());
    }
}
