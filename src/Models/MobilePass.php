<?php

namespace Spatie\LaravelMobilePass\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\Traits\HasPassData;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePass extends Model
{
    use HasFactory, HasUuids, HasPassData;

    public static function boot()
    {
        parent::boot();

        static::retrieved(function (MobilePass $mobilePass) {
            self::uncompileContent($mobilePass);
        });

        static::updated(function (MobilePass $mobilePass) {
            /** @var class-string<NotifyAppleOfPassUpdateAction> $action */
            $action = Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);

            app($action)->execute($mobilePass);
        });

        static::saving(function (MobilePass $mobilePass) {
            self::compileContent($mobilePass);
        });
    }

    public function registrations(): HasMany
    {
        $modelClass = Config::mobilePassRegistrationModel();

        return $this->hasMany($modelClass, 'pass_serial');
    }

    public function devices(): HasManyThrough
    {
        $modelClass = Config::mobilePassRegistrationModel();
        $deviceModelClass = Config::deviceModel();

        return $this->hasManyThrough($deviceModelClass, $modelClass, 'pass_serial', 'id', 'id', 'device_id');
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
            'webServiceURL' => config('mobile-pass.apple.webservice.host').'/passkit/',
            'teamIdentifier' => $model->teamIdentifier ?? config('mobile-pass.team_identifier'),
            'description' => $model->description,
            'serialNumber' => $model->getKey(),
            'backgroundColor' => (string) $model->backgroundColour,
            'foregroundColor' => (string) $model->foregroundColour,
            'labelColor' => (string) $model->labelColour,
            'barcodes' => array_map(fn ($barcode) => $barcode->toArray(), $model->barcodes),
            'voided' => $model->voided,
            'userInfo' => [
                'passType' => $model->passType->value,
            ],

            $model->passType->value => self::compileFields($model),
        ]);
    }

    public static function getCertificatePath(): string
    {
        if (! empty(config('mobile-pass.apple.certificate_contents'))) {
            $path = sys_get_temp_dir().'/LaravelMobilePass.p12';

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

    public function wasUpdatedAfter(?Carbon $since = null): bool
    {
        if (! $since) {
            return true;
        }

        return $this->updated_at > $since;
    }
}
