<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * Registering a Device to Receive Push Notifications for a Pass
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW2
 */
class RegisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePass::findOrFail($request->passSerial);

        $pass->registrations()->create([
            'device_id' => $request->deviceId,
            'pass_type_id' => $request->passTypeId,
            'pass_serial' => $request->passSerial,
            'push_token' => $request->pushToken,
        ]);
    }
}
