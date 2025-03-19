<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

/**
 * Getting the Serial Numbers for Passes Associated with a Device
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW4
 */
class GetAssociatedSerialsForDeviceController extends Controller
{
    public function __invoke()
    {
        $registrations = MobilePassRegistration::where([
            'device_id' => $request->deviceId,
            'pass_type_id' => $request->passId,
        ]);

        if (request()->has('passesUpdatedSince')) {
            $since = Carbon::createFromTimestamp(request()->get('passesUpdatedSince'));

            $registrations->whereHas('pass', fn ($q) => $q->where('updated_at', '>', $since));
        }

        $lastUpdated = $registrations->max('pass.updated_at');
        $results = $registrations->get();

        return response()->json([
            'lastUpdated' => $lastUpdated->toIso8601String(),
            'serialNumbers' => $results->pluck('pass_serial')->all(),
        ]);
    }
}
