<?php

namespace Spatie\LaravelMobilePass\Validators;

use Illuminate\Validation\Rule;
use Spatie\LaravelMobilePass\Enums\TransitType;

class BoardingPassValidator extends PassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'boardingPass.transitType' => [
                'required',
                Rule::enum(TransitType::class),
            ],
            'boardingPass.headerFields' => ['nullable', 'array'],
            'boardingPass.primaryFields' => ['nullable', 'array'],
            'boardingPass.secondaryFields' => ['nullable', 'array'],
            'boardingPass.auxiliaryFields' => ['nullable', 'array'],
            'boardingPass.backFields' => ['nullable', 'array'],
        ]);
    }
}
