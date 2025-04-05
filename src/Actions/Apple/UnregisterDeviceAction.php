<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
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
            ])->each(fn (AppleMobilePassRegistration $registration) => $registration->delete());
    }
}
