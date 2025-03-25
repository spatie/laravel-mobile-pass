<?php

namespace Spatie\LaravelMobilePass\Models\Traits;

use PHPUnit\Exception;
use Spatie\LaravelMobilePass\Entities\Barcode;
use Spatie\LaravelMobilePass\Entities\Colour;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\TransitType;


trait HasPassData
{
    public ?string $organisationName = null;

    public ?string $passTypeIdentifier = null;

    public ?string $authenticationToken = null;

    public ?string $teamIdentifier = null;

    public ?string $description = null;

    public array $headerFields = [];

    public array $primaryFields = [];

    public array $secondaryFields = [];

    public array $auxiliaryFields = [];

    public ?Colour $backgroundColour = null;

    public ?Colour $labelColour = null;

    public ?Colour $foregroundColour = null;

    public array $passImages = [];

    public PassType $passType = PassType::Generic;

    public array $barcodes = [];

    public ?bool $voided = null;

    protected function casts()
    {
        return [
            'content' => 'json',
            'images' => 'json',
        ];
    }

    public function setType(PassType $passType): self
    {
        $this->passType = $passType;

        return $this;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function setLogoImage(Image $image): self
    {
        $this->passImages['logo'] = $image;

        return $this;
    }

    public function setIconImage(Image $image): self
    {
        $this->passImages['icon'] = $image;

        return $this;
    }

    public function setFooterImage(Image $image): self
    {
        $this->passImages['footer'] = $image;

        return $this;
    }

    public function setStripImage(Image $image): self
    {
        $this->passImages['strip'] = $image;

        return $this;
    }

    public function setBackgroundColour(Colour $colour): self
    {
        $this->backgroundColour = $colour;

        return $this;
    }

    public function setForegroundColour(Colour $colour): self
    {
        $this->foregroundColour = $colour;

        return $this;
    }

    public function setLabelColour(Colour $colour): self
    {
        $this->labelColour = $colour;

        return $this;
    }

    public function addBarcodes(Barcode ...$barcodes)
    {
        $this->barcodes = $barcodes;

        return $this;
    }

    public function void(bool $state = true)
    {
        $this->voided = $state;

        return $this;
    }

    public function addHeaderFields(FieldContent ...$fieldContent)
    {
        foreach ($fieldContent as $field) {
            $this->headerFields[$field->key] = $field;
        }

        return $this;
    }

    public function addPrimaryFields(FieldContent ...$fieldContent)
    {
        foreach ($fieldContent as $field) {
            $this->primaryFields[$field->key] = $field;
        }

        return $this;
    }

    public function addSecondaryFields(FieldContent ...$fieldContent)
    {
        foreach ($fieldContent as $field) {
            $this->secondaryFields[$field->key] = $field;
        }

        return $this;
    }

    public function addAuxiliaryFields(FieldContent ...$fieldContent)
    {
        foreach ($fieldContent as $field) {
            $this->auxiliaryFields[$field->key] = $field;
        }

        return $this;
    }

    public function updateFieldValueByKey(string $key, string $value)
    {
        // Find the field by key and update it
        $field = $this->headerFields[$key] ?? $this->primaryFields[$key] ?? $this->secondaryFields[$key] ?? $this->auxiliaryFields[$key] ?? null;

        if (! $field) {
            throw new Exception('Key not found');
        }

        $field->value = $value;

        return $this;
    }

    protected static function uncompileFieldSet(self $model, string $fieldSetName)
    {
        $model->$fieldSetName = [];

        foreach ($model->content[$model->passType->value][$fieldSetName] ?? [] as $field) {
            $model->$fieldSetName[$field['key']] = FieldContent::fromArray($field);
        }
    }

    protected static function uncompileContent(self $model)
    {
        $model->organisationName = $model->content['organisationName'] ?? null;
        $model->passTypeIdentifier = $model->content['passTypeIdentifier'] ?? null;
        $model->authenticationToken = $model->content['authenticationToken'] ?? null;
        $model->teamIdentifier = $model->content['teamIdentifier'] ?? null;
        $model->description = $model->content['description'] ?? null;
        $model->backgroundColour = Colour::makeFromRgbString($model->content['backgroundColor'] ?? null);
        $model->foregroundColour = Colour::makeFromRgbString($model->content['foregroundColor'] ?? null);
        $model->labelColour = Colour::makeFromRgbString($model->content['labelColor'] ?? null);
        $model->passType = PassType::tryFrom($model->content['userInfo']['passType'] ?? PassType::Generic->value);
        $model->voided = $model->content['voided'] ?? null;

        $model->passImages = array_map(fn ($image) => Image::fromArray($image), $model->images);

        $model->barcodes = array_map(fn ($barcode) => Barcode::fromArray($barcode), $model->content['barcodes'] ?? []);

        self::uncompileFieldSet($model, 'headerFields');
        self::uncompileFieldSet($model, 'primaryFields');
        self::uncompileFieldSet($model, 'secondaryFields');
        self::uncompileFieldSet($model, 'auxiliaryFields');
    }

    protected static function compileFields(self $model)
    {
        return [
            'transitType' => TransitType::Air, // todo: put this somewhere else
            'headerFields' => array_map(fn ($field) => $field->toArray(), array_values($model->headerFields ?? [])),
            'primaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->primaryFields ?? [])),
            'secondaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->secondaryFields ?? [])),
            'auxiliaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->auxiliaryFields ?? [])),
        ];
    }
}
