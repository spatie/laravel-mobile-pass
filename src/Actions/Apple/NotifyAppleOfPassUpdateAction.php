<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\AppleWalletRequestFailed;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

class NotifyAppleOfPassUpdateAction
{
    /**
     * APNs reasons that mean the registration can never receive a push again.
     *
     * @var array<int, string>
     */
    protected array $invalidRegistrationReasons = [
        'BadDeviceToken',
        'DeviceTokenNotForTopic',
    ];

    public function execute(MobilePass $mobilePass): void
    {
        $failures = [];

        // One failing device must not keep the other devices from hearing about the update,
        // so every registration is notified before the first failure is rethrown.
        $mobilePass->registrations()->with('device')->get()->each(
            function (AppleMobilePassRegistration $registration) use ($mobilePass, &$failures) {
                $registration->setRelation('pass', $mobilePass);

                try {
                    $this->notifyUpdate($registration);
                } catch (AppleWalletRequestFailed $exception) {
                    $failures[] = $exception;
                }
            },
        );

        if ($failures !== []) {
            foreach (array_slice($failures, 1) as $failure) {
                report($failure);
            }

            throw $failures[0];
        }
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

        if ($response->status() === 400 && in_array($response->json('reason'), $this->invalidRegistrationReasons, true)) {
            $registration->delete();

            return;
        }

        if ($response->failed()) {
            throw AppleWalletRequestFailed::fromResponse($response, $this->updateUrl($registration));
        }
    }
}
