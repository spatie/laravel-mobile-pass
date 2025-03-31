<?php

namespace Spatie\LaravelMobilePass\Actions;

use Google\Client;
use GuzzleHttp\ClientInterface;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Illuminate\Support\Str;

class CreateGooglePassClass
{
    protected const APP_NAME = 'Wallet';
    protected const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    // TODO: get class type from model?
    protected const BASE_URL = 'https://walletobjects.googleapis.com/walletobjects/v1/loyaltyClass';

    public string $baseUrl;
    public string $issuerId;
    public ClientInterface $handler;

    public function __construct()
    {
        $client = new Client;
        $client->setApplicationName(self::APP_NAME);
        $client->setScopes([self::SCOPE]);
        $client->setAuthConfig(json_decode(config('mobile-pass.google.auth'), true));

        $this->baseUrl = self::BASE_URL;
        $this->handler = $client->authorize();
        $this->issuerId = config('mobile-pass.google.issuer_id');
    }

    public function execute(MobilePass $mobilePass)
    {
        // TODO: what should the classId be?
        // It needs to be unique to the type of pass we're generating,
        // like a template. But _not_ unique to each pass we generate.
        $classId = (string) Str::uuid();

        $payload = [
            'json' => [
                'id' => $classId,
                'reviewStatus' => 'underReview',
                'issuerName' => config('mobile-pass.organisation_name'),
                'homepageUri' => [
                    'uri' => config('app.url'),
                ],
            ]
        ];

        $response = $this->handler->post("{$this->baseUrl}", $payload);

        $response = json_decode($response->getBody());

        if (!empty($response->error)) {
            throw new \Exception($response->error->message);
        }

        return $classId;
    }
}
