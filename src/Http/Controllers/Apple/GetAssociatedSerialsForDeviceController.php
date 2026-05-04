<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Http\Requests\Apple\GetAssociatedSerialsForDeviceRequest;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;

/**
 * Getting the Serial Numbers for Passes Associated with a Device
 * https://developer.apple.com/documentation/walletpasses/get-the-list-of-updatable-passes
 */
class GetAssociatedSerialsForDeviceController extends Controller
{
    public function __invoke(GetAssociatedSerialsForDeviceRequest $request): Response|JsonResponse
    {
        $updatedSince = $request->passesUpdatedSince();

        $registrations = $request
            ->registrationsQuery()
            ->with('pass')
            ->when($updatedSince, fn (Builder $query) => $query->whereHas(
                'pass',
                fn (Builder $passQuery) => $passQuery->where('updated_at', '>', $updatedSince),
            ))
            ->get();

        if ($registrations->isEmpty()) {
            return response()->noContent();
        }

        return response()->json($this->responseData($registrations));
    }

    /**
     * @return array{lastUpdated: string, serialNumbers: array<int, string>}
     */
    protected function responseData(Collection $registrations): array
    {
        $lastUpdated = $registrations
            ->map(fn (AppleMobilePassRegistration $registration) => $registration->pass->updated_at)
            ->max()
            ->toIso8601ZuluString();

        return [
            'lastUpdated' => $lastUpdated,
            'serialNumbers' => $registrations->pluck('pass.pass_serial')->all(),
        ];
    }
}
