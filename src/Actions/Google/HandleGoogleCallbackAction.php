<?php

namespace Spatie\LaravelMobilePass\Actions\Google;

use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class HandleGoogleCallbackAction
{
    public function execute(Request $request): void
    {
        /** @var array<string, mixed> $claims */
        $claims = (array) $request->attributes->get('google_callback_claims', []);

        $objectId = $claims['objectId'] ?? null;

        if ($objectId === null) {
            return;
        }

        $eventType = match ($claims['eventType'] ?? null) {
            'save' => 'save',
            'del' => 'remove',
            default => null,
        };

        if ($eventType === null) {
            return;
        }

        $mobilePass = $this->resolvePass((string) $objectId);

        if ($mobilePass === null) {
            return;
        }

        $eventModelClass = Config::googleMobilePassEventModel();

        $event = $eventModelClass::query()->create([
            'mobile_pass_id' => $mobilePass->id,
            'event_type' => $eventType,
            'received_at' => now(),
            'raw_payload' => $claims,
        ]);

        event(match ($eventType) {
            'save' => new GoogleMobilePassSaved($mobilePass, $event),
            'remove' => new GoogleMobilePassRemoved($mobilePass, $event),
        });
    }

    protected function resolvePass(string $objectId): ?MobilePass
    {
        $modelClass = Config::mobilePassModel();

        return $modelClass::query()
            ->where('content->googleObjectId', $objectId)
            ->first();
    }
}
