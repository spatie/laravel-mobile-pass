<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleJwtSigner
{
    protected const TOKEN_CACHE_KEY = 'mobile-pass.google.access-token';

    protected const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    /** @param array<string, mixed> $payload */
    public function signSaveUrlJwt(array $payload): string
    {
        $claims = [
            'iss' => GoogleCredentials::clientEmail(),
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'origins' => config('mobile-pass.google.origins', []),
            'payload' => $payload,
        ];

        return JWT::encode($claims, GoogleCredentials::privateKey(), 'RS256');
    }

    public function accessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->signAssertionJwt(),
        ])->throw();

        $token = (string) $response->json('access_token');
        $ttl = max(60, ((int) $response->json('expires_in', 3600)) - 30);

        Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    protected function signAssertionJwt(): string
    {
        $now = time();

        $claims = [
            'iss' => GoogleCredentials::clientEmail(),
            'scope' => self::SCOPE,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        return JWT::encode($claims, GoogleCredentials::privateKey(), 'RS256');
    }
}
