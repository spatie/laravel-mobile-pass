<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelMobilePass\Enums\DataDetectorType;
use Spatie\LaravelMobilePass\Enums\NumberStyleType;

class FieldContent implements Arrayable
{
    public ?string $attributedValue = null;

    public ?string $value = null;

    public ?string $label = null;

    public ?NumberStyleType $numberStyle = null;

    public ?string $changeMessage = null;

    public ?string $currencyCode = null;

    public ?DataDetectorType $dataType = null;

    public ?bool $ignoresTimezone = null;

    public ?bool $isRelative = null;

    public function __construct(
        public string $key
    ) {}

    public static function fromArray(array $fields)
    {
        $fieldContent = new static(
            $fields['key'],
        );

        $fieldContent->attributedValue = $fields['attributedValue'] ?? null;
        $fieldContent->value = $fields['value'] ?? null;
        $fieldContent->label = $fields['label'] ?? null;
        $fieldContent->numberStyle = NumberStyleType::tryFrom($fields['numberStyle'] ?? null);
        $fieldContent->changeMessage = $fields['changeMessage'] ?? null;
        $fieldContent->currencyCode = $fields['currencyCode'] ?? null;
        $fieldContent->dataType = DataDetectorType::tryFrom($fields['dataType'] ?? null);
        $fieldContent->ignoresTimezone = $fields['ignoresTimezone'] ?? null;
        $fieldContent->isRelative = $fields['isRelative'] ?? null;

        return $fieldContent;
    }

    public static function make(string $key): static
    {
        return new static(
            key: $key,
        );
    }

    public function withAttributedValue(string $attributedValue): self
    {
        $this->attributedValue = $attributedValue;

        return $this;
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

    public function ignoreTimezone(): self
    {
        $this->ignoresTimezone = true;

        return $this;
    }

    public function showDateAsRelative(): self
    {
        $this->isRelative = true;

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
