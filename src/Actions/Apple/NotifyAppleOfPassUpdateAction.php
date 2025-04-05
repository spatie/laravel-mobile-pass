<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class NotifyAppleOfPassUpdateAction
{
    public function execute(MobilePass $mobilePass): void
    {
        $mobilePass
            ->registrations
            ->each(
                fn (MobilePassRegistration $registration) => $this->notifyUpdate($registration)
            );
    }

    protected function headers(MobilePassRegistration $registration): array
    {
        return [
            'apns-topic' => $registration->pass_type_id,
        ];
    }

    protected function options(MobilePassRegistration $registration): array
    {
        return [
            'version' => 2.0,
            'cert' => [
                $registration->pass->builder()->getCertificatePath(),
                $registration->pass->builder()->getCertificatePassword(),
            ],
        ];
    }

    protected function updateUrl(MobilePassRegistration $registration): string
    {
        return $registration->appleUpdateUrl();
    }

    protected function notifyUpdate(MobilePassRegistration $registration): self
    {
        Http::withHeaders($this->headers($registration))
            ->withOptions($this->options($registration))
            ->post(
                url: $this->updateUrl($registration),
                data: json_decode('{}'),
            );

        return $this;
    }
}
