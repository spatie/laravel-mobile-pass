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
            'preferredStyleSchemes' => ['nullable', 'array'],
            'footerBackgroundColor' => ['nullable', 'string'],
            'stripColor' => ['nullable', 'string'],
            'suppressHeaderDarkening' => ['nullable', 'boolean'],
            'useAutomaticColors' => ['nullable', 'boolean'],
        ]);
    }
}
