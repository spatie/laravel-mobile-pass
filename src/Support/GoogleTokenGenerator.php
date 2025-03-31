<?php

namespace Spatie\LaravelMobilePass\Support;

use Firebase\JWT\JWT;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GoogleTokenGenerator
{
    protected const TOKEN_AUDIENCE = 'google';

    protected const TOKEN_TYPE = 'savetowallet';

    protected const ENCODE_ALGORITHM = 'RS256';

    public function generate(MobilePass $mobilePass): string
    {
        $auth = json_decode(config('mobile-pass.google.auth'));

        $token = [
            'iss' => config('mobile-pass.google.pass_issuer'),
            'aud' => self::TOKEN_AUDIENCE,
            'typ' => self::TOKEN_TYPE,
            'iat' => now()->timestamp,
            'payload' => $mobilePass->builder()->compileObjectForGoogle(),
            'origins' => config('mobile-pass.google.download_origins'),
        ];

        return JWT::encode(
            $token,
            $auth->private_key,
            self::ENCODE_ALGORITHM
        );
    }
}
