<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * Unregistering a Device
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW5
 */
class UnregisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePass::findOrFail($request->passSerial);

        // Do we have a registration for this device?
        $registration = $pass->registrations()->where('device_id', $request->deviceId)->first();

        if ($registration) {
            $registration->delete();
        }
    }
}
