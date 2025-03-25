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
use Spatie\LaravelMobilePass\Support\DownloadableMobilePass;

class MobilePass extends Model
{
    use HasFactory, HasPassData, HasUuids;

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

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
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

    protected static function compileContent(MobilePass $model)
    {
        $model->images = $model->passImages;

        $model->load('template');

        $model->content = array_filter([
            'formatVersion' => 1,
            'organizationName' => $model->organisationName ?? $model->template?->organisationName ?? config('mobile-pass.organisation_name'),
            'passTypeIdentifier' => $model->passTypeIdentifier ?? config('mobile-pass.type_identifier'),
            'authenticationToken' => config('mobile-pass.apple.webservice.secret'),
            'webServiceURL' => config('mobile-pass.apple.webservice.host').'/passkit/',
            'teamIdentifier' => $model->teamIdentifier ?? config('mobile-pass.team_identifier'),
            'description' => $model->description,
            'serialNumber' => $model->getKey(),
            'backgroundColor' => (string) ($model->backgroundColour ?? $model->template?->backgroundColour),
            'foregroundColor' => (string) ($model->foregroundColour ?? $model->template?->foregroundColour),
            'labelColor' => (string) ($model->labelColour ?? $model->template?->labelColour),
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

    public function download(string $name = 'pass'): DownloadableMobilePass
    {
        return new DownloadableMobilePass($this->generate(), $name);
    }

    public function wasUpdatedAfter(?Carbon $since = null): bool
    {
        if (! $since) {
            return true;
        }

        return $this->updated_at > $since;
    }
}
