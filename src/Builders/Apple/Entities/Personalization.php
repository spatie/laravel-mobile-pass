<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelMobilePass\Enums\PersonalizationField;

class Personalization implements Arrayable
{
    /** @param  array<int, PersonalizationField>  $requiredPersonalizationFields */
    public function __construct(
        public string $description,
        public array $requiredPersonalizationFields,
        public ?string $termsAndConditions = null,
    ) {}

    /** @param  array<int, PersonalizationField>  $requiredPersonalizationFields */
    public static function make(
        string $description,
        array $requiredPersonalizationFields,
        ?string $termsAndConditions = null,
    ): self {
        return new self($description, $requiredPersonalizationFields, $termsAndConditions);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            description: $values['description'],
            requiredPersonalizationFields: array_map(
                fn (string $field) => PersonalizationField::from($field),
                $values['requiredPersonalizationFields'] ?? [],
            ),
            termsAndConditions: $values['termsAndConditions'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requiredPersonalizationFields' => array_map(
                fn (PersonalizationField $field) => $field->value,
                $this->requiredPersonalizationFields,
            ),
            'description' => $this->description,
            'termsAndConditions' => $this->termsAndConditions,
        ], fn ($value) => $value !== null);
    }
}
