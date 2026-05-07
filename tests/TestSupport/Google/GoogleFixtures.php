<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Google;

class GoogleFixtures
{
    public static function serviceAccountPath(): string
    {
        return __DIR__.'/../google-service-account.json';
    }

    public static function serviceAccountContents(): string
    {
        return (string) file_get_contents(self::serviceAccountPath());
    }

    public static function privateKey(): string
    {
        $decoded = json_decode(self::serviceAccountContents(), true, flags: JSON_THROW_ON_ERROR);

        return $decoded['private_key'];
    }

    public static function publicKey(): string
    {
        return (string) file_get_contents(__DIR__.'/../google-public-key.pem');
    }

    /**
     * Load the bundled P-256 root keypair used to sign intermediate keys in
     * ECv2SigningOnly callback tests.
     *
     * The PEM files in this directory were generated once with:
     *   openssl ecparam -name prime256v1 -genkey -noout -out ecv2-root-key.pem
     *
     * They're committed because PHP 8.4's `openssl_pkey_new` rejects key sizes
     * under 384 bits, which excludes the P-256 curve Google's spec mandates,
     * and we don't want tests to depend on the system `openssl` binary.
     *
     * @return array{private: string, public_base64: string}
     */
    public static function ecv2RootKeypair(): array
    {
        return self::loadEcKeypair(__DIR__.'/ecv2-root-key.pem');
    }

    /** @return array{private: string, public_base64: string} */
    public static function ecv2IntermediateKeypair(): array
    {
        return self::loadEcKeypair(__DIR__.'/ecv2-intermediate-key.pem');
    }

    /**
     * A second, unrelated root keypair. Used to seed the cache with "stale"
     * keys when a test needs to simulate Google rotating its root signing keys.
     *
     * @return array{private: string, public_base64: string}
     */
    public static function ecv2StaleRootKeypair(): array
    {
        return self::loadEcKeypair(__DIR__.'/ecv2-stale-root-key.pem');
    }

    /**
     * Build a Google Wallet ECv2SigningOnly callback payload, signed end-to-end:
     *
     * 1. The intermediate signing key is signed with the root private key over
     *    `senderId || protocolVersion || signedKey`.
     * 2. The signed message is signed with the intermediate private key over
     *    `senderId || recipientId || protocolVersion || signedMessage`.
     *
     * Each component is length-prefixed with a 4-byte little-endian uint32, per
     * Google's spec.
     *
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    public static function buildEcv2CallbackPayload(
        string $rootPrivatePem,
        string $intermediatePrivatePem,
        string $intermediatePublicBase64,
        string $issuerId,
        array $message,
        ?int $intermediateExpirationMs = null,
    ): array {
        $intermediateExpirationMs ??= (int) round((microtime(true) + 86400) * 1000);

        $signedKey = (string) json_encode([
            'keyValue' => $intermediatePublicBase64,
            'keyExpiration' => (string) $intermediateExpirationMs,
        ]);

        $intermediateSignedString = self::lengthPrefixedConcat([
            'GooglePayPasses',
            'ECv2SigningOnly',
            $signedKey,
        ]);

        $intermediateSignature = self::ecdsaSign($rootPrivatePem, $intermediateSignedString);

        $signedMessage = (string) json_encode($message);

        $messageSignedString = self::lengthPrefixedConcat([
            'GooglePayPasses',
            $issuerId,
            'ECv2SigningOnly',
            $signedMessage,
        ]);

        $messageSignature = self::ecdsaSign($intermediatePrivatePem, $messageSignedString);

        return [
            'protocolVersion' => 'ECv2SigningOnly',
            'intermediateSigningKey' => [
                'signedKey' => $signedKey,
                'signatures' => [base64_encode($intermediateSignature)],
            ],
            'signature' => base64_encode($messageSignature),
            'signedMessage' => $signedMessage,
        ];
    }

    /**
     * The shape of the `keys` array returned by Google's root keys endpoint.
     *
     * @return array{keys: array<int, array<string, string>>}
     */
    public static function rootKeysResponse(string $rootPublicBase64, ?int $rootExpirationMs = null): array
    {
        $rootExpirationMs ??= (int) round((microtime(true) + 7 * 86400) * 1000);

        return [
            'keys' => [
                [
                    'keyValue' => $rootPublicBase64,
                    'protocolVersion' => 'ECv2SigningOnly',
                    'keyExpiration' => (string) $rootExpirationMs,
                ],
            ],
        ];
    }

    /** @return array{private: string, public_base64: string} */
    private static function loadEcKeypair(string $privatePemPath): array
    {
        $privatePem = (string) file_get_contents($privatePemPath);

        $key = openssl_pkey_get_private($privatePem);

        if ($key === false) {
            throw new \RuntimeException("Could not load EC private key from {$privatePemPath}");
        }

        $publicPem = openssl_pkey_get_details($key)['key'];

        $publicBase64 = (string) preg_replace(
            '/\s+|-----(BEGIN|END) PUBLIC KEY-----/',
            '',
            $publicPem,
        );

        return [
            'private' => $privatePem,
            'public_base64' => $publicBase64,
        ];
    }

    /** @param  list<string>  $parts */
    private static function lengthPrefixedConcat(array $parts): string
    {
        $output = '';

        foreach ($parts as $part) {
            $output .= pack('V', strlen($part)).$part;
        }

        return $output;
    }

    private static function ecdsaSign(string $privatePem, string $data): string
    {
        openssl_sign($data, $signature, $privatePem, OPENSSL_ALGO_SHA256);

        return $signature;
    }
}
