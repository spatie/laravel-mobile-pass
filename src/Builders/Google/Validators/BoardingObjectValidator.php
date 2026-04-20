<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class BoardingObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'classId' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'passengerName' => ['nullable', 'string'],
            'boardingAndSeatingInfo' => ['nullable', 'array'],
            'reservationInfo' => ['nullable', 'array'],
            'barcode' => ['nullable', 'array'],
        ];
    }
}
