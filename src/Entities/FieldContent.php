<?php

namespace Spatie\LaravelMobilePass\Entities;

use Spatie\LaravelMobilePass\Enums\DataDetectorType;
use Spatie\LaravelMobilePass\Enums\NumberStyleType;

class FieldContent
{
    public ?string $value = null;
    public ?string $label = null;
    public ?NumberStyleType $numberStyle = null;
    public ?string $changeMessage = null;
    public ?string $currencyCode = null;
    public ?DataDetectorType $dataType = null;

    public function __construct(
        public string $key
    )
    {
    }

    public static function make(string $key): static
    {
        return new static(
            key: $key,
        );
    }

    public function withLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function withValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function usingNumberStyle(NumberStyleType $numberStyle): self
    {
        $this->numberStyle = $numberStyle;

        return $this;
    }

    public function showMessageWhenChanged(string $changeMessage): self
    {
        $this->changeMessage = $changeMessage;

        return $this;
    }

    public function usingCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function asDataType(DataDetectorType $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function toArray()
    {
        return array_filter([
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
        ]);
    }
}                   