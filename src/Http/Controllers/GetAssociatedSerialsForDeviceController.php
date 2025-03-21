<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

/**
 * Getting the Serial Numbers for Passes Associated with a Device
 * https://developer.apple.com/documentation/walletpasses/get-the-list-of-updatable-passes
 */
class GetAssociatedSerialsForDeviceController extends Controller
{
    public function __invoke(Request $request)
    {
        $registrations = MobilePassRegistration::where([
            'device_id' => $request->deviceId,
            'pass_type_id' => $request->passTypeId,
        ]);

        if (request()->query('passesUpdatedSince')) {
            $since = Carbon::parse($request->query('passesUpdatedSince'));

            $registrations->whereHas('pass', fn ($q) => $q->where('updated_at', '>', $since));
        }

        // For each registration, get the last updated time of each pass.
        $results = $registrations->get();

        if ($results->isEmpty()) {
            return response([], 204);
        }

        return response()->json([
            'lastUpdated' => $results->map->pass->pluck('updated_at')->max()->toIso8601ZuluString(),
            'serialNumbers' => $results->pluck('pass_serial')->all(),
        ]);
    }
}
