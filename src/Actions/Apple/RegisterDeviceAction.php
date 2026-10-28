<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        // The pass type ID comes from the URL and is used as the APNs topic, so it must
        // belong to the pass. A wrong topic makes every push for this pass fail.
        if (($pass->content['passTypeIdentifier'] ?? null) !== $passTypeId) {
            throw (new ModelNotFoundException)->setModel($pass::class, [$passSerial]);
        }

        $device = $this->device($deviceId, $pushToken);

        // createOrFirst relies on the unique (device_id, mobile_pass_id) index, so a retried
        // concurrent request can't store the same device twice.
        $registration = $pass->registrations()->createOrFirst(
            ['device_id' => $device->getKey()],
            ['pass_type_id' => $passTypeId],
        );

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
