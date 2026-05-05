<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class EventTicketObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'classId' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'ticketHolderName' => ['nullable', 'string'],
            'seatInfo' => ['nullable', 'array'],
            'barcode' => ['nullable', 'array'],
        ];
    }
}
