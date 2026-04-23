<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use PKPass\PKPassException;

class InvalidCertificate extends Exception implements MobilePassException
{
    public static function fromPkPassException(PKPassException $previous): self
    {
        return new self(
            "The Apple Wallet pass-signing certificate could not be loaded. "
            .'Verify MOBILE_PASS_APPLE_CERTIFICATE_PATH or MOBILE_PASS_APPLE_CERTIFICATE, '
            .'confirm MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD is correct, and check that '
            ."the certificate has not expired. Original error: {$previous->getMessage()}",
            0,
            $previous,
        );
    }
}
