<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

/**
 * Getting the Serial Numbers for Passes Associated with a Device
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW4
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

            $registrations->whereHas('pass', fn ($q) => $q->whereDate('updated_at', '>', $since));
        }

        // For each registration, get the last updated time of each pass.
        $results = $registrations->get();
        $lastUpdated = $results->map->pass->pluck('updated_at')->max();

        return response()->json([
            'lastUpdated' => $lastUpdated?->toIso8601ZuluString() ?? null,
            'serialNumbers' => $results->pluck('pass_serial')->all(),
        ]);
    }
}
