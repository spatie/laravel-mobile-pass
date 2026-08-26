<?php

namespace Spatie\LaravelMobilePass\Http\Requests\Apple;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class PersonalizeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'personalizationToken' => ['required', 'string'],
            'requiredPersonalizationInfo' => ['required', 'array'],
        ];
    }

    public function mobilePass(): MobilePass
    {
        $mobilePassClass = Config::mobilePassModel();

        return $mobilePassClass::query()
            ->where('pass_serial', $this->route('passSerial'))
            ->firstOrFail();
    }
}
