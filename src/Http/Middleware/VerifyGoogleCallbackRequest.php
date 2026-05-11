<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class VerifyGoogleCallbackRequest
{
    private const SENDER_ID = 'GooglePayPasses';

    private const PROTOCOL_VERSION = 'ECv2SigningOnly';

    private const ROOT_KEYS_URL = 'https://pay.google.com/gp/m/issuer/keys';

    private const ROOT_KEYS_CACHE_KEY = 'mobile-pass.google.root-keys';

    private const ROOT_KEYS_FALLBACK_TTL_SECONDS = 1800;

    private const ROOT_KEYS_MIN_TTL_SECONDS = 60;

    private const ROOT_KEYS_TTL_SAFETY_MARGIN_SECONDS = 60;

    private const ROOT_KEYS_HTTP_TIMEOUT_SECONDS = 5;

    private const ROOT_KEYS_HTTP_RETRY_ATTEMPTS = 2;

    private const ROOT_KEYS_HTTP_RETRY_DELAY_MS = 200;

    public function handle(Request $request, Closure $next): mixed
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            throw new AuthenticationException('Invalid Google callback payload.');
        }

        if (($payload['protocolVersion'] ?? null) !== self::PROTOCOL_VERSION) {
            throw new AuthenticationException('Unsupported Google callback protocol version.');
        }

        $issuerId = (string) config('mobile-pass.google.issuer_id');

        if ($issuerId === '') {
            throw new AuthenticationException('No Google issuer id configured.');
        }

        try {
            $claims = $this->verifyAndDecode($payload, $issuerId);
        } catch (Throwable $exception) {
            throw new AuthenticationException('Invalid Google callback signature: '.$exception->getMessage());
        }

        $request->attributes->set('google_callback_claims', $claims);

        return $next($request);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function verifyAndDecode(array $payload, string $issuerId): array
    {
        $cachedKeys = Cache::get(self::ROOT_KEYS_CACHE_KEY);

        if (is_array($cachedKeys) && $this->extractUsableKeyValues($cachedKeys) !== []) {
            // The endpoint is public, so an attacker can spam forged payloads.
            // Refetching on every signature failure would turn each forgery
            // into an outbound pay.google.com round-trip — a cheap DoS
            // amplification vector. Trust the TTL/safety-margin to refresh
            // keys before they expire and fail fast on signature failure.
            return $this->verifyWithKeys($payload, $issuerId, $cachedKeys);
        }

        $freshKeys = $this->fetchRootKeysFromGoogle();
        $this->cacheRootKeys($freshKeys);

        return $this->verifyWithKeys($payload, $issuerId, $freshKeys);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, mixed>  $keys
     * @return array<string, mixed>
     */
    private function verifyWithKeys(array $payload, string $issuerId, array $keys): array
    {
        $usableKeys = $this->extractUsableKeyValues($keys);

        if ($usableKeys === []) {
            throw new RuntimeException('No usable Google root keys available.');
        }

        $intermediateKey = $this->verifyIntermediateSigningKey($payload, $usableKeys);
        $signedMessage = $this->verifySignedMessage($payload, $intermediateKey, $issuerId);

        $claims = json_decode($signedMessage, true);

        if (! is_array($claims)) {
            throw new RuntimeException('Signed message is not valid JSON.');
        }

        return $claims;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $rootKeys
     */
    private function verifyIntermediateSigningKey(array $payload, array $rootKeys): string
    {
        $intermediate = $payload['intermediateSigningKey'] ?? null;

        if (! is_array($intermediate)) {
            throw new RuntimeException('Missing intermediateSigningKey.');
        }

        $signedKey = (string) ($intermediate['signedKey'] ?? '');
        $signatures = $intermediate['signatures'] ?? [];

        if ($signedKey === '' || ! is_array($signatures) || $signatures === []) {
            throw new RuntimeException('Missing signedKey or signatures.');
        }

        $signedString = $this->buildSignedString([
            self::SENDER_ID,
            self::PROTOCOL_VERSION,
            $signedKey,
        ]);

        $verified = false;

        foreach ($signatures as $signature) {
            foreach ($rootKeys as $rootKey) {
                if ($this->verifySignature($signedString, (string) $signature, $rootKey)) {
                    $verified = true;
                    break 2;
                }
            }
        }

        if (! $verified) {
            throw new RuntimeException('Intermediate signing key signature failed verification.');
        }

        $signedKeyData = json_decode($signedKey, true);

        if (! is_array($signedKeyData)) {
            throw new RuntimeException('signedKey is not valid JSON.');
        }

        $expiration = (int) ($signedKeyData['keyExpiration'] ?? 0);

        if ($expiration <= $this->nowMillis()) {
            throw new RuntimeException('Intermediate signing key has expired.');
        }

        $keyValue = (string) ($signedKeyData['keyValue'] ?? '');

        if ($keyValue === '') {
            throw new RuntimeException('Missing keyValue in intermediate signing key.');
        }

        return $keyValue;
    }

    /** @param  array<string, mixed>  $payload */
    private function verifySignedMessage(array $payload, string $intermediateKey, string $issuerId): string
    {
        $signature = (string) ($payload['signature'] ?? '');
        $signedMessage = (string) ($payload['signedMessage'] ?? '');

        if ($signature === '' || $signedMessage === '') {
            throw new RuntimeException('Missing signature or signedMessage.');
        }

        $signedString = $this->buildSignedString([
            self::SENDER_ID,
            $issuerId,
            self::PROTOCOL_VERSION,
            $signedMessage,
        ]);

        if (! $this->verifySignature($signedString, $signature, $intermediateKey)) {
            throw new RuntimeException('Message signature failed verification.');
        }

        return $signedMessage;
    }

    /** @param  list<string>  $parts */
    private function buildSignedString(array $parts): string
    {
        $output = '';

        foreach ($parts as $part) {
            $output .= pack('V', strlen($part)).$part;
        }

        return $output;
    }

    private function verifySignature(string $signedString, string $base64Signature, string $base64PublicKey): bool
    {
        $signature = base64_decode($base64Signature, true);

        if ($signature === false) {
            return false;
        }

        $key = openssl_pkey_get_public($this->base64ToPem($base64PublicKey));

        if ($key === false) {
            return false;
        }

        return openssl_verify($signedString, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    private function base64ToPem(string $base64): string
    {
        if (str_contains($base64, '-----BEGIN')) {
            return $base64;
        }

        $chunked = chunk_split($base64, 64, "\n");

        return "-----BEGIN PUBLIC KEY-----\n{$chunked}-----END PUBLIC KEY-----\n";
    }

    /** @return array<int, mixed> */
    private function fetchRootKeysFromGoogle(): array
    {
        $response = Http::timeout(self::ROOT_KEYS_HTTP_TIMEOUT_SECONDS)
            ->retry(self::ROOT_KEYS_HTTP_RETRY_ATTEMPTS, self::ROOT_KEYS_HTTP_RETRY_DELAY_MS, throw: false)
            ->get(self::ROOT_KEYS_URL);

        if ($response->failed()) {
            throw new RuntimeException('Failed to fetch Google root keys.');
        }

        return (array) $response->json('keys', []);
    }

    /**
     * @param  array<int, mixed>  $keys
     * @return list<string>
     */
    private function extractUsableKeyValues(array $keys): array
    {
        $now = $this->nowMillis();
        $usable = [];

        foreach ($keys as $key) {
            if (! is_array($key)) {
                continue;
            }

            if (($key['protocolVersion'] ?? null) !== self::PROTOCOL_VERSION) {
                continue;
            }

            $expiration = isset($key['keyExpiration']) ? (int) $key['keyExpiration'] : null;

            if ($expiration !== null && $expiration <= $now) {
                continue;
            }

            $keyValue = (string) ($key['keyValue'] ?? '');

            if ($keyValue !== '') {
                $usable[] = $keyValue;
            }
        }

        return $usable;
    }

    /** @param  array<int, mixed>  $keys */
    private function cacheRootKeys(array $keys): void
    {
        Cache::put(self::ROOT_KEYS_CACHE_KEY, $keys, $this->resolveCacheTtlSeconds($keys));
    }

    /** @param  array<int, mixed>  $keys */
    private function resolveCacheTtlSeconds(array $keys): int
    {
        $now = $this->nowMillis();
        $earliestExpiration = null;

        foreach ($keys as $key) {
            if (! is_array($key) || ! isset($key['keyExpiration'])) {
                continue;
            }

            $expiration = (int) $key['keyExpiration'];

            if ($expiration <= $now) {
                continue;
            }

            if ($earliestExpiration === null || $expiration < $earliestExpiration) {
                $earliestExpiration = $expiration;
            }
        }

        if ($earliestExpiration === null) {
            return self::ROOT_KEYS_FALLBACK_TTL_SECONDS;
        }

        $secondsUntilExpiration = intdiv($earliestExpiration - $now, 1000);
        $ttl = $secondsUntilExpiration - self::ROOT_KEYS_TTL_SAFETY_MARGIN_SECONDS;

        return min($secondsUntilExpiration, max(self::ROOT_KEYS_MIN_TTL_SECONDS, $ttl));
    }

    private function nowMillis(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}
