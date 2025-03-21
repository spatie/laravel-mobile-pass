<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;

/**
 * Registering a Device to Receive Push Notifications for a Pass
 * https://developer.apple.com/documentation/walletpasses/register-a-pass-for-update-notifications
 */
class RegisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePass::findOrFail($request->passSerial);

        // Do we already have a device record?
        $device = MobilePassDevice::updateOrCreate([
            'id' => $request->deviceId,
        ], [
            'push_token' => $request->get('pushToken'),
        ]);

        $registration = $pass->registrations()->firstOrCreate([
            'device_id' => $device->getKey(),
            'pass_type_id' => $request->passTypeId,
            'pass_serial' => $request->passSerial,
        ]);

        return response(
            null,
            $registration->wasRecentlyCreated ? 201 : 200
        );
    }
}
