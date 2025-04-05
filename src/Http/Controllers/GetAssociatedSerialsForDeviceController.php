<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Http\Requests\GetAssociatedSerialsForDeviceRequest;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;

/**
 * Getting the Serial Numbers for Passes Associated with a Device
 * https://developer.apple.com/documentation/walletpasses/get-the-list-of-updatable-passes
 */
class GetAssociatedSerialsForDeviceController extends Controller
{
    public function __invoke(GetAssociatedSerialsForDeviceRequest $request)
    {
        $registrations = $request
            ->registrationsQuery()
            ->when($request->passesUpdatedSince(), function (Builder $query) use ($request) {
                $query->whereHas('pass', function (Builder $query) use ($request) {
                    $query->where('updated_at', '>', $request->passesUpdatedSince());
                });
            })
            ->get();

        if ($registrations->isEmpty()) {
            return response()->noContent();
        }

        return response()->json($this->responseData($registrations));
    }

    protected function responseData(Collection $registrations): array
    {
        $lastUpdated = $registrations
            ->map(fn (AppleMobilePassRegistration $registration) => $registration->pass->updated_at)
            ->max()
            ->toIso8601ZuluString();

        return [
            'lastUpdated' => $lastUpdated,
            'serialNumbers' => $registrations->pluck('pass_serial')->all(),
        ];
    }
}
