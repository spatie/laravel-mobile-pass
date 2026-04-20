<?php

namespace Spatie\LaravelMobilePass\Actions\Google;

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;

class NotifyGoogleOfPassUpdateAction
{
    public function __construct(protected GoogleWalletClient $client) {}

    public function execute(MobilePass $mobilePass): void
    {
        $googleClassType = $mobilePass->content['googleClassType'] ?? null;
        $objectId = $mobilePass->content['googleObjectId'] ?? null;

        if (! $googleClassType || ! $objectId) {
            return;
        }

        $resource = str_replace('Class', 'Object', $googleClassType);

        $this->client->patchObject($resource, $objectId, $mobilePass->content['googleObjectPayload'] ?? []);
    }
}
