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

    public function addPosterField(
        string $key,
        string $value,
        PosterFieldType $type = PosterFieldType::Primary,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

        $this->storeField($type->value, $field);

        return $this;
    }

    protected function fieldProperties(): array
    {
        return array_merge(
            parent::fieldProperties(),
            array_column(PosterFieldType::cases(), 'value'),
        );
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
                'posterGeneric' => array_filter($this->compilePosterFields()),
            ],
        );
    }

    /** @return array<string, array<int, array<string, mixed>>|null> */
    protected function compilePosterFields(): array
    {
        $posterFields = [];

        foreach (PosterFieldType::cases() as $type) {
            $posterFields[$type->jsonKey()] = $this->{$type->value}?->values()->toArray();
        }

        return $posterFields;
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $posterGeneric = $this->data['posterGeneric'] ?? [];

        foreach (PosterFieldType::cases() as $type) {
            $this->{$type->value} = collect();

            foreach ($posterGeneric[$type->jsonKey()] ?? [] as $field) {
                $this->{$type->value}[$field['key']] = FieldContent::fromArray($field);
            }
        }
    }
}
