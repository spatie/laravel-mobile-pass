<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelMobilePass\Models\Traits\HasPassData;

class Template extends Model
{
    use HasPassData, HasUuids;

    public $table = 'mobile_pass_templates';

    public static function boot()
    {
        parent::boot();

        static::retrieved(function (self $mobilePass) {
            self::uncompileContent($mobilePass);
        });

        // static::updated(function (MobilePass $mobilePass) {
        //     /** @var class-string<NotifyAppleOfPassUpdateAction> $action */
        //     // $action = Config::getActionClass('notify_apple_of_pass_update', NotifyAppleOfPassUpdateAction::class);

        //     // app($action)->execute($mobilePass);
        // });

        static::saving(function (self $mobilePass) {
            self::compileContent($mobilePass);
        });
    }

    public static function compileContent(self $model)
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

    public function passes()
    {
        return $this->hasMany(MobilePass::class);
    }
}
