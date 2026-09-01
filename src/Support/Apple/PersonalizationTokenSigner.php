<?php

namespace Spatie\LaravelMobilePass\Support\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\ApplePassBuilder;
use Spatie\LaravelMobilePass\Exceptions\InvalidCertificate;

class PersonalizationTokenSigner
{
    public function sign(string $token): string
    {
        $tokenPath = tempnam(sys_get_temp_dir(), 'personalization-token');
        $signaturePath = tempnam(sys_get_temp_dir(), 'personalization-signature');

        try {
            file_put_contents($tokenPath, $token);

            [$cert, $privateKey] = $this->readCertificate();

            $signed = openssl_pkcs7_sign(
                $tokenPath,
                $signaturePath,
                $cert,
                $privateKey,
                [],
                PKCS7_BINARY | PKCS7_DETACHED,
            );

            if (! $signed) {
                throw InvalidCertificate::fromPkcs7SignFailure();
            }

            $signature = file_get_contents($signaturePath);

            return $this->convertPemToDer($signature);
        } finally {
            // Always clean up, whether signing succeeded or an exception was thrown above.
            unlink($tokenPath);
            unlink($signaturePath);
        }
    }

    /** @return array{0: \OpenSSLCertificate|string, 1: \OpenSSLAsymmetricKey|string} */
    protected function readCertificate(): array
    {
        $certificatePath = ApplePassBuilder::getCertificatePath();
        $certificatePassword = ApplePassBuilder::getCertificatePassword();

        $p12Content = file_get_contents($certificatePath);

        if (! openssl_pkcs12_read($p12Content, $certs, $certificatePassword)) {
            throw InvalidCertificate::fromPkcs12ReadFailure();
        }

        $cert = openssl_x509_read($certs['cert']);
        $privateKey = openssl_pkey_get_private($certs['pkey'], $certificatePassword);

        return [$cert, $privateKey];
    }

    protected function convertPemToDer(string $signature): string
    {
        $begin = 'filename="smime.p7s"';
        $end = '------';

        $signature = substr($signature, strpos($signature, $begin) + strlen($begin));
        $signature = substr($signature, 0, strpos($signature, $end));
        $signature = trim($signature);

        return base64_decode($signature);
    }
}
