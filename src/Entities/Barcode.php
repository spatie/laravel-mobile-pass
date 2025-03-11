<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

class Barcode implements Arrayable
{
    public ?string $altText = null;

    public function __construct(
        public BarcodeType $format,
        public string $message,
        public string $messageEncoding
    )
    {
    }

    public static function make(BarcodeType $format, string $message, string $messageEncoding): static
    {
        return new static(
            format: $format,
            message: $message,
            messageEncoding: $messageEncoding
        );
    }

    public function withAltText(string $altText): self
    {
        $this->altText = $altText;

        return $this;
    }

    public function toArray()
    {
        return array_filter([
            'format' => $this->format->value,
            'message' => $this->message,
            'messageEncoding' => $this->messageEncoding,
            'altText' => $this->altText,
        ]);
    }
}                   