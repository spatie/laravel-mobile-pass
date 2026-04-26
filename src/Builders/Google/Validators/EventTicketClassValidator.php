<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class EventTicketClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'eventName' => ['required', 'array'],
            'eventName.defaultValue.value' => ['required', 'string'],
            'eventName.defaultValue.language' => ['required', 'string'],
            'venue' => ['nullable', 'array'],
            'venue.name' => ['nullable', 'array'],
            'venue.address' => ['nullable', 'array'],
            'dateTime' => ['nullable', 'array'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
