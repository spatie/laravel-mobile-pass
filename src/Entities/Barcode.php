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
        public string $messageEncoding = 'iso-8859-1'
    ) {}

    public static function make(BarcodeType $format, string $message, string $messageEncoding = 'iso-8859-1'): static
    {
        return new static(
            format: $format,
            message: $message,
            messageEncoding: $messageEncoding
        );
    }

    public static function fromArray(array $fields)
    {
        $barcode = new static(
            BarcodeType::tryFrom($fields['format']),
            $fields['message'],
            $fields['messageEncoding']
        );

        $barcode->altText = $fields['altText'] ?? null;

        return $barcode;
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
