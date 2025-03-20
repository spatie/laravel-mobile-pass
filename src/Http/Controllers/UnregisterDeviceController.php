<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

/**
 * Unregistering a Device
 * https://developer.apple.com/documentation/walletpasses/unregister-a-pass-for-update-notifications
 */
class UnregisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePassRegistration::where([
            'device_id' => $request->deviceId,
            'pass_serial' => $request->passSerial,
        ])->first();

        $pass?->delete();
    }
}
