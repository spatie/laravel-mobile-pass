<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

class EventTicketApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'eventTicket.headerFields' => ['nullable', 'array'],
            'eventTicket.primaryFields' => ['nullable', 'array'],
            'eventTicket.secondaryFields' => ['nullable', 'array'],
            'eventTicket.auxiliaryFields' => ['nullable', 'array'],
            'eventTicket.backFields' => ['nullable', 'array'],
        ]);
    }
}
