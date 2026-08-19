<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\GenericApplePassValidator;
use Spatie\LaravelMobilePass\Enums\DateType;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\PosterFieldType;
use Spatie\LaravelMobilePass\Enums\TimeStyleType;

class GenericPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::Generic;

    protected ?Collection $posterHeaderFields = null;

    protected ?Collection $posterPrimaryFields = null;

    protected ?Collection $posterFooterFields = null;

    protected ?Collection $posterBackFields = null;

    protected static function validator(): ApplePassValidator
    {
        return new GenericApplePassValidator;
    }

    public function addPosterHeaderField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Header, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterPrimaryField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Primary, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterFooterField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Footer, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterBackField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Back, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    protected function addPosterField(
        string $key,
        string $value,
        PosterFieldType $type,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

        $property = $type->value;

        $this->{$property} ??= collect();
        $this->{$property}[$key] = $field;

        return $this;
    }

    public function updateField(
        string $key,
        string $value,
        ?string $changeMessage = null,
        ?string $label = null,
    ): self {
        parent::updateField($key, $value, $changeMessage, $label);

        foreach (PosterFieldType::cases() as $type) {
            $property = $type->value;

            if ($this->{$property} === null) {
                continue;
            }

            $this->{$property} = $this->{$property}->map(
                fn (FieldContent $field) => $field->key === $key
                    ? $this->applyFieldUpdate($field, $value, $changeMessage, $label)
                    : $field,
            );
        }

        return $this;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'generic' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                    'backFields' => $this->backFields?->values()->toArray(),
                ]),
                'posterGeneric' => array_filter([
                    'headerFields' => $this->posterHeaderFields?->values()->toArray(),
                    'primaryFields' => $this->posterPrimaryFields?->values()->toArray(),
                    'footerFields' => $this->posterFooterFields?->values()->toArray(),
                    'backFields' => $this->posterBackFields?->values()->toArray(),
                ]),
            ],
        );
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $posterGeneric = $this->data['posterGeneric'] ?? [];

        $propertyToJsonKey = [
            'posterHeaderFields' => 'headerFields',
            'posterPrimaryFields' => 'primaryFields',
            'posterFooterFields' => 'footerFields',
            'posterBackFields' => 'backFields',
        ];

        foreach ($propertyToJsonKey as $property => $jsonKey) {
            $this->{$property} = collect();

            foreach ($posterGeneric[$jsonKey] ?? [] as $field) {
                $this->{$property}[$field['key']] = FieldContent::fromArray($field);
            }
        }
    }
}
