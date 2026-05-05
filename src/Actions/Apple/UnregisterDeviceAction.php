<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Events\MobilePassRemoved;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class UnregisterDeviceAction
{
    public function execute(string $deviceId, string $passSerial): void
    {
        $pass = $this->mobilePass($passSerial);

        if ($pass === null) {
            return;
        }

        $mobilePassRegistrationModel = Config::appleMobilePassRegistrationModel();

        $mobilePassRegistrationModel::query()
            ->where([
                'device_id' => $deviceId,
                'mobile_pass_id' => $pass->getKey(),
            ])
            ->each(function (AppleMobilePassRegistration $registration) use ($pass) {
                $registration->delete();

                event(new MobilePassRemoved($pass));
            });
    }

    protected function mobilePass(string $passSerial): ?MobilePass
    {
        $mobilePassModel = Config::mobilePassModel();

        return $mobilePassModel::query()
            ->where('pass_serial', $passSerial)
            ->first();
    }
}
