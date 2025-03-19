<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * Registering a Device to Receive Push Notifications for a Pass
 * https://developer.apple.com/documentation/walletpasses/register-a-pass-for-update-notifications
 */
class RegisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePass::findOrFail($request->passSerial);

        return $pass->registrations()->create([
            'device_id' => $request->deviceId,
            'pass_type_id' => $request->passTypeId,
            'pass_serial' => $request->passSerial,
            'push_token' => $request->pushToken,
        ]);
    }
}
