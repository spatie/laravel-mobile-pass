<?php

namespace Spatie\LaravelMobilePass\Models;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Entities\Barcode;
use Spatie\LaravelMobilePass\Entities\Colour;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\TransitType;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePass extends Model
{
    use HasUuids;

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

    public array $passImages = [];

    public PassType $passType = PassType::Generic;

    public array $barcodes = [];

    public function setType(PassType $passType): self
    {
        $this->passType = $passType;

        return $this;
    }

    public static function boot()
    {
        parent::boot();

        static::retrieved(function (MobilePass $mobilePass) {
            self::uncompileContent($mobilePass);
        });

        static::updated(function (MobilePass $mobilePass) {
            $actionClass = Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);

            app($actionClass)->execute($mobilePass);
        });

        static::saving(function (MobilePass $mobilePass) {
            self::compileContent($mobilePass);
        });
    }

    public function registrations(): HasMany
    {
        $modelClass = Config::modelPassRegistrationModel();

        return $this->hasMany($modelClass, 'pass_serial');
    }

    protected function casts()
    {
        return [
            'content' => 'json',
            'images' => 'json',
        ];
    }

    protected static function uncompileContent(MobilePass $model)
    {
        $model->organisationName = $model->content['organisationName'] ?? null;
        $model->passTypeIdentifier = $model->content['passTypeIdentifier'] ?? null;
        $model->authenticationToken = $model->content['authenticationToken'] ?? null;
        $model->teamIdentifier = $model->content['teamIdentifier'] ?? null;
        $model->description = $model->content['description'] ?? null;
        $model->backgroundColour = Colour::makeFromRgbString($model->content['backgroundColor'] ?? null);
        $model->labelColour = Colour::makeFromRgbString($model->content['labelColor'] ?? null);
        $model->passType = PassType::tryFrom($model->content['userInfo']['passType'] ?? PassType::Generic);

        $model->passImages = array_map(fn ($image) => Image::fromArray($image), $model->images);

        $model->barcodes = array_map(fn ($barcode) => Barcode::fromArray($barcode), $model->content['barcodes'] ?? []);

        self::uncompileFieldSet($model, 'headerFields');
        self::uncompileFieldSet($model, 'primaryFields');
        self::uncompileFieldSet($model, 'secondaryFields');
        self::uncompileFieldSet($model, 'auxiliaryFields');
    }

    protected static function uncompileFieldSet(MobilePass $model, string $fieldSetName)
    {
        $model->$fieldSetName = [];

        foreach ($model->content[$model->passType->value][$fieldSetName] ?? [] as $field) {
            $model->$fieldSetName[$field['key']] = FieldContent::fromArray($field);
        }
    }

    protected static function compileContent(MobilePass $model)
    {
        $model->images = $model->passImages;

        $model->content = array_filter([
            'formatVersion' => 1,
            'organizationName' => $model->organisationName ?? config('mobile-pass.organisation_name'),
            'passTypeIdentifier' => $model->passTypeIdentifier ?? config('mobile-pass.type_identifier'),
            'authenticationToken' => config('mobile-pass.apple.webservice.secret'),
            'webServiceURL' => config('app.url').'/passkit/', // TODO: Must be HTTPS. Point this to your server. config('app.url'),
            'teamIdentifier' => $model->teamIdentifier ?? config('mobile-pass.team_identifier'),
            'description' => $model->description,
            'serialNumber' => $model->getKey(),
            'backgroundColor' => (string) $model->backgroundColour,
            'labelColor' => (string) $model->labelColour,
            'barcodes' => array_map(fn ($barcode) => $barcode->toArray(), $model->barcodes),
            'userInfo' => [
                'passType' => $model->passType->value,
            ],

            $model->passType->value => self::compileFields($model),
        ]);
    }

    protected static function compileFields(MobilePass $model)
    {
        return [
            'transitType' => TransitType::Air, // todo: put this somewhere else
            'headerFields' => array_map(fn ($field) => $field->toArray(), array_values($model->headerFields ?? [])),
            'primaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->primaryFields ?? [])),
            'secondaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->secondaryFields ?? [])),
            'auxiliaryFields' => array_map(fn ($field) => $field->toArray(), array_values($model->auxiliaryFields ?? [])),
        ];
    }

    public static function getCertificatePath(): string
    {
        if (! empty(config('mobile-pass.apple.certificate_contents'))) {
            $path = __DIR__.'/../../tmp/Cert.p12';

            if (! file_exists($path)) {
                file_put_contents(
                    $path,
                    base64_decode(
                        config('mobile-pass.apple.certificate_contents')
                    )
                );
            }

            return $path;
        }

        return config('mobile-pass.apple.certificate_path');
    }

    public static function getCertificatePassword(): string
    {
        return config('mobile-pass.apple.certificate_password');
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

    public function setBackgroundColour(Colour $colour): self
    {
        $this->backgroundColour = $colour;

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

    protected function addImagesToFile(PKPass $pkPass): PKPass
    {
        foreach ($this->passImages as $filename => $image) {
            // The $image Image entity could contain up to three
            // images in different resolutions.

            if ($image->x1Path) {
                $pkPass->addFile($image->x1Path, "$filename.png");
            }

            if ($image->x2Path) {
                $pkPass->addFile($image->x2Path, "$filename@2x.png");
            }

            if ($image->x3Path) {
                $pkPass->addFile($image->x3Path, "$filename@3x.png");
            }
        }

        return $pkPass;
    }

    public function generate()
    {
        $pkPass = new PKPass(
            self::getCertificatePath(),
            self::getCertificatePassword(),
        );

        self::compileContent($this);

        $pkPass->setData(
            $this->content
        );

        $this->addImagesToFile($pkPass);

        return $pkPass->create(output: false);
    }
}
