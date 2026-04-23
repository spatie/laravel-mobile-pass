<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Events\MobilePassRemoved;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Support\Config;

class UnregisterDeviceAction
{
    public function execute(string $deviceId, string $passSerial): void
    {
        $mobilePassRegistrationModel = Config::appleMobilePassRegistrationModel();

        $mobilePassRegistrationModel::query()
            ->with('pass')
            ->where([
                'device_id' => $deviceId,
                'pass_serial' => $passSerial,
            ])
            ->each(function (AppleMobilePassRegistration $registration) {
                $pass = $registration->pass;

                $registration->delete();

                event(new MobilePassRemoved($pass));
            });
    }
}
