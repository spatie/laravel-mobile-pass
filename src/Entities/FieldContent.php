<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelMobilePass\Enums\DataDetectorType;
use Spatie\LaravelMobilePass\Enums\DateType;
use Spatie\LaravelMobilePass\Enums\NumberStyleType;
use Spatie\LaravelMobilePass\Enums\TextAlignmentType;
use Spatie\LaravelMobilePass\Enums\TimeStyleType;

/**
 * https://developer.apple.com/documentation/walletpasses/passfieldcontent
 */
class FieldContent implements Arrayable
{
    public ?string $attributedValue = null;

    public ?string $value = null;

    public ?string $label = null;

    public ?NumberStyleType $numberStyle = null;

    public ?string $changeMessage = null;

    public ?string $currencyCode = null;

    public ?DateType $dateStyle = null;

    public ?TimeStyleType $timeStyle = null;

    public ?DataDetectorType $dataDetectorType = null;

    public ?bool $ignoresTimezone = null;

    public ?bool $isRelative = null;

    public ?TextAlignmentType $textAlignment = null;

    public function __construct(
        public string $key
    ) {}

    public static function fromArray(array $fields): static
    {
        $fieldContent = new static(
            $fields['key'],
        );

        $fieldContent->attributedValue = $fields['attributedValue'] ?? null;
        $fieldContent->value = $fields['value'] ?? null;
        $fieldContent->label = $fields['label'] ?? null;
        $fieldContent->numberStyle = ! empty($fields['numberStyle']) ? NumberStyleType::tryFrom($fields['numberStyle']) : null;
        $fieldContent->changeMessage = $fields['changeMessage'] ?? null;
        $fieldContent->currencyCode = $fields['currencyCode'] ?? null;
        $fieldContent->dataDetectorType = ! empty($fields['dataDetectorTypes']) ? DataDetectorType::tryFrom($fields['dataDetectorType']) : null;
        $fieldContent->dateStyle = ! empty($fields['dateStyle']) ? DateType::tryFrom($fields['dateStyle']) : null;
        $fieldContent->ignoresTimezone = $fields['ignoresTimezone'] ?? null;
        $fieldContent->isRelative = $fields['isRelative'] ?? null;
        $fieldContent->textAlignment = ! empty($fields['textAlignment']) ? TextAlignmentType::tryFrom($fields['textAlignment']) : null;

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

    public function usingDateType(DateType $dateType): self
    {
        $this->dateStyle = $dateType;

        return $this;
    }

    public function usingTimeType(TimeStyleType $timeType): self
    {
        $this->timeStyle = $timeType;

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
        $this->dataDetectorType = $dataType;

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

    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'attributedValue' => $this->attributedValue,
            'changeMessage' => $this->changeMessage,
            'currencyCode' => $this->currencyCode,
            'dataDetectorTypes' => $this->dataDetectorType?->value,
            'dateStyle' => $this->dateStyle?->value,
            'ignoresTimezone' => $this->ignoresTimezone,
            'isRelative' => $this->isRelative,
            'numberStyle' => $this->numberStyle?->value,
            'textAlignment' => $this->textAlignment?->value,
            'timeStyle' => $this->timeStyle?->value,
        ]);
    }
}
