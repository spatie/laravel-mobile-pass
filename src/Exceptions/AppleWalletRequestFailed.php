<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class AppleWalletRequestFailed extends Exception implements MobilePassException
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
        public readonly string $endpoint,
    ) {
        parent::__construct("Apple Wallet APNs returned {$status} for {$endpoint}: {$body}");
    }

    public static function fromResponse(Response $response, string $endpoint): self
    {
        return new self($response->status(), (string) $response->body(), $endpoint);
    }
}
