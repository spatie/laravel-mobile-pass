<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Actions\RegisterDeviceAction;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * Registering a Device to Receive Push Notifications for a Pass
 * https://developer.apple.com/documentation/walletpasses/register-a-pass-for-update-notifications
 */
class RegisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var class-string<RegisterDeviceAction> $actionClass */
        $actionClass = Config::getActionClass('register_device', RegisterDeviceAction::class);

        $registration = (new $actionClass)->execute(
            $request->deviceId,
            $request->get('pushToken'),
            $request->passTypeId,
            $request->passSerial,
        );

        return response()
            ->noContent()
            ->setStatusCode($registration->wasRecentlyCreated ? 201 : 200);
    }
}
