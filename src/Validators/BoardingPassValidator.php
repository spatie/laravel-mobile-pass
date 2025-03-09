<?php

namespace Spatie\LaravelMobilePass\Validators;

use Illuminate\Validation\Rule;
use Spatie\LaravelMobilePass\Enums\TransitType;

class BoardingPassValidator extends PassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'boardingPass' => [
                'transitType' => [
                    'required',
                    Rule::enum(TransitType::class),
                ],
            ],
        ]);
    }
}
