<?php

namespace Spatie\LaravelMobilePass\Listeners;

use GuzzleHttp\Client;
use Spatie\LaravelMobilePass\Events\MobilePassUpdatedEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\Registration;

class NotifyAppleOfPassUpdate
{
    public function handle(MobilePassUpdatedEvent $mobilePass): void
    {
        $mobilePass->registrations->each(function (Registration $registration) {
            $this->notifyUpdate($registration);
        });
    }

    protected function notifyUpdate(Registration $registration): self
    {
        $url = config('mobile-pass.apple.apple_push_base_url')."/{$registration->push_token}";

        app(Client::class)
            ->post($url, [
                'headers' => [
                    'apns-topic' => config('mobile-pass.type_identifier'),
                ],
                'json' => json_decode('{}'),
                'version' => 2.0,
                'cert' => [
                    MobilePass::getCertificatePath(),
                    MobilePass::getCertificatePassword(),
                ],
            ]);

        return $this;
    }
}
