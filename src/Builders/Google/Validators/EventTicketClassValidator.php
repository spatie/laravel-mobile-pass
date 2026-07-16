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
            'eventName.translatedValues' => ['nullable', 'array'],
            'eventName.translatedValues.*.language' => ['nullable', 'string'],
            'eventName.translatedValues.*.value' => ['nullable', 'string'],
            'venue' => ['nullable', 'array'],
            'venue.name' => ['nullable', 'array'],
            'venue.name.defaultValue.value' => ['nullable', 'string'],
            'venue.name.defaultValue.language' => ['nullable', 'string'],
            'venue.name.translatedValues' => ['nullable', 'array'],
            'venue.name.translatedValues.*.language' => ['nullable', 'string'],
            'venue.name.translatedValues.*.value' => ['nullable', 'string'],
            'venue.address' => ['nullable', 'array'],
            'venue.address.defaultValue.value' => ['nullable', 'string'],
            'venue.address.defaultValue.language' => ['nullable', 'string'],
            'venue.address.translatedValues' => ['nullable', 'array'],
            'venue.address.translatedValues.*.language' => ['nullable', 'string'],
            'venue.address.translatedValues.*.value' => ['nullable', 'string'],
            'dateTime' => ['nullable', 'array'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
