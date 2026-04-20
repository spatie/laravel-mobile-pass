<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class BoardingClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'issuerName' => ['nullable', 'string'],
            'localScheduledDepartureDateTime' => ['nullable', 'string'],
            'flightHeader' => ['nullable', 'array'],
            'flightHeader.carrier' => ['nullable', 'array'],
            'flightHeader.carrier.airlineCode' => ['nullable', 'string'],
            'flightHeader.flightNumber' => ['nullable', 'string'],
            'origin' => ['nullable', 'array'],
            'origin.airportIataCode' => ['nullable', 'string'],
            'destination' => ['nullable', 'array'],
            'destination.airportIataCode' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
            'hexBackgroundColor' => ['nullable', 'string'],
            'reviewStatus' => ['nullable', 'string'],
        ];
    }
}
