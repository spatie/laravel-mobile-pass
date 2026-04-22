<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class NfcPayload implements Arrayable
{
    public function __construct(
        public string $message,
        public string $encryptionPublicKey,
        public bool $requiresAuthentication = false,
    ) {}

    public static function make(
        string $message,
        string $encryptionPublicKey,
        bool $requiresAuthentication = false,
    ): self {
        return new self($message, $encryptionPublicKey, $requiresAuthentication);
    }

    /** @param array<string, mixed> $values */
    public static function fromArray(array $values): self
    {
        return new self(
            (string) $values['message'],
            (string) $values['encryptionPublicKey'],
            (bool) ($values['requiresAuthentication'] ?? false),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'encryptionPublicKey' => $this->encryptionPublicKey,
            'requiresAuthentication' => $this->requiresAuthentication ?: null,
        ], fn ($value) => $value !== null);
    }
}
