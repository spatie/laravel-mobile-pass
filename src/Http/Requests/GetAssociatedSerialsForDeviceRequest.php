<?php

namespace Spatie\LaravelMobilePass\Http\Requests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Support\Config;

class GetAssociatedSerialsForDeviceRequest extends FormRequest
{
    public function registrationsQuery(): Builder
    {
        $registrationsModel = Config::mobilePassRegistrationModel();

        return $registrationsModel::where([
            'device_id' => $this->route('deviceId'),
            'pass_type_id' => $this->route('passTypeId'),
        ]);
    }

    public function passesUpdatedSince(): ?Carbon
    {
        $queryValue = $this->query('passesUpdatedSince');

        if (! $queryValue) {
            return null;
        }

        return Carbon::parse($this->query('passesUpdatedSince'));

    }
}
