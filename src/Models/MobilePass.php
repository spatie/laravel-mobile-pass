<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Enums\TransitType;
use Spatie\LaravelMobilePass\Events\MobilePassUpdated;

class MobilePass extends Model
{
    use HasUuids;

    public $table = 'mobile_passes';

    public ?string $organisationName = null;
    public ?string $passTypeIdentifier = null;
    public ?string $authenticationToken = null;
    public ?string $teamIdentifier = null;
    public ?string $description = null;

    public array $headerFields = [];
    public array $primaryFields = [];
    public array $secondaryFields = [];
    public array $auxiliaryFields = [];

    public array $images = [];

    protected $dispatchesEvents = [
        'updated' => MobilePassUpdated::class,
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'pass_serial');
    }

    protected function casts()
    {
        return [
            'content' => 'json',
            'images' => 'json',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::retrieved(function (MobilePass $model) {
            self::uncompileContent($model);
        });

        static::saving(function (MobilePass $model) {
            self::compileContent($model);
        });
    }

    protected static function uncompileContent(MobilePass $model)
    {
        $passType = 'boardingPass'; // TODO: make this dynamic

        $model->organisationName = $model->content['organisationName'] ?? null;
        $model->passTypeIdentifier = $model->content['passTypeIdentifier'] ?? null;
        $model->authenticationToken = $model->content['authenticationToken'] ?? null;
        $model->teamIdentifier = $model->content['teamIdentifier'] ?? null;
        $model->description = $model->content['description'] ?? null;

        $model->headerFields = array_map(fn ($field) => FieldContent::fromArray($field), $model->content[$passType]['headerFields'] ?? []);
        $model->primaryFields = array_map(fn ($field) => FieldContent::fromArray($field), $model->content[$passType]['primaryFields'] ?? []);
        $model->secondaryFields = array_map(fn ($field) => FieldContent::fromArray($field), $model->content[$passType]['secondaryFields'] ?? []);
        $model->auxiliaryFields = array_map(fn ($field) => FieldContent::fromArray($field), $model->content[$passType]['auxiliaryFields'] ?? []);
    }

    protected static function compileContent(MobilePass $model)
    {
        $passType = 'boardingPass'; // TODO: make this dynamic

        $model->content = [
            'formatVersion' => 1,
            'organizationName' => $model->organisationName ?? config('mobile-pass.organisation_name'),
            'passTypeIdentifier' => $model->passTypeIdentifier ?? config('mobile-pass.type_identifier'),
            // 'authenticationToken' => $model->authenticationToken,
            'teamIdentifier' => $model->teamIdentifier ?? config('mobile-pass.team_identifier'),
            'description' => $model->description,
            'serialNumber' => $model->getKey(),
            $passType => self::compileFields($model),
        ];
    }

    protected static function compileFields(MobilePass $model)
    {
        return [
            'transitType' => TransitType::Air, // todo: put this somewhere else
            'headerFields' => array_map(fn ($field) => $field->toArray(), $model->headerFields),
            'primaryFields' => array_map(fn ($field) => $field->toArray(), $model->primaryFields),
            'secondaryFields' => array_map(fn ($field) => $field->toArray(), $model->secondaryFields),
            'auxiliaryFields' => array_map(fn ($field) => $field->toArray(), $model->auxiliaryFields),
        ];
    }

    protected static function getCertificatePath(): string
    {
        if (! empty(config('mobile-pass.apple.certificate_contents'))) {
            $path = __DIR__.'/../../tmp/Cert.p12';

            file_put_contents(
                $path,
                base64_decode(
                    config('mobile-pass.apple.certificate_contents')
                )
            );

            return $path;
        }

        return config('mobile-pass.apple.certificate_path');
    }

    protected static function getCertificatePassword(): string
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
        $this->images['logo'] = $image;

        return $this;
    }

    public function setIconImage(Image $image): self
    {
        $this->images['icon'] = $image;

        return $this;
    }

    public function addHeaderFields(FieldContent ...$fieldContent)
    {
        $this->headerFields = $fieldContent;

        return $this;
    }

    public function addPrimaryFields(FieldContent ...$fieldContent)
    {
        $this->primaryFields = $fieldContent;

        return $this;
    }

    public function addSecondaryFields(FieldContent ...$fieldContent)
    {
        $this->secondaryFields = $fieldContent;

        return $this;
    }

    protected function addImagesToFile(PKPass $pkPass): PKPass
    {
        foreach ($this->images as $filename => $image) {
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
