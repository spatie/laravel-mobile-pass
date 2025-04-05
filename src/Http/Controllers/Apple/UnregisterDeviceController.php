<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Actions\Apple\UnregisterDeviceAction;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * Unregistering a Device
 * https://developer.apple.com/documentation/walletpasses/unregister-a-pass-for-update-notifications
 */
class UnregisterDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var class-string<UnregisterDeviceAction> $action */
        $action = Config::getActionClass('unregister_device', UnregisterDeviceAction::class);

        (new $action)->execute($request->deviceId, $request->passSerial);

        return response()->noContent();
    }
}
