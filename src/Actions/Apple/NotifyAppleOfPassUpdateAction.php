<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\AppleWalletRequestFailed;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

class NotifyAppleOfPassUpdateAction
{
    public function execute(MobilePass $mobilePass): void
    {
        $mobilePass->registrations->each(
            fn (AppleMobilePassRegistration $registration) => $this->notifyUpdate($registration),
        );
    }

    /** @return array<string, string> */
    protected function headers(AppleMobilePassRegistration $registration): array
    {
        return [
            'apns-topic' => $registration->pass_type_id,
        ];
    }

    /** @return array<string, mixed> */
    protected function options(AppleMobilePassRegistration $registration): array
    {
        $builder = $registration->pass->builder();

        return [
            'version' => 2.0,
            'cert' => [
                $builder->getCertificatePath(),
                $builder->getCertificatePassword(),
            ],
        ];
    }

    protected function updateUrl(AppleMobilePassRegistration $registration): string
    {
        return $registration->appleUpdateUrl();
    }

    protected function notifyUpdate(AppleMobilePassRegistration $registration): self
    {
        $response = Http::withHeaders($this->headers($registration))
            ->withOptions($this->options($registration))
            ->post(
                url: $this->updateUrl($registration),
                data: json_decode('{}'),
            );

        $this->handleResponse($registration, $response);

        return $this;
    }

    protected function handleResponse(AppleMobilePassRegistration $registration, Response $response): void
    {
        if ($response->status() === 410) {
            $registration->delete();

            return;
        }

        if ($response->failed()) {
            throw AppleWalletRequestFailed::fromResponse($response, $this->updateUrl($registration));
        }
    }
}
