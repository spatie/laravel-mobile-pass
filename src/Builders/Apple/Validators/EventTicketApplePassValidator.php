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
            'suppressHeaderDarkening' => ['nullable', 'boolean'],
            'useAutomaticColors' => ['nullable', 'boolean'],
            'accessibilityURL' => ['nullable', 'string'],
            'addOnURL' => ['nullable', 'string'],
            'auxiliaryStoreIdentifiers' => ['nullable', 'array'],
            'bagPolicyURL' => ['nullable', 'string'],
            'contactVenueEmail' => ['nullable', 'string'],
            'contactVenuePhoneNumber' => ['nullable', 'string'],
            'contactVenueWebsite' => ['nullable', 'string'],
            'directionsInformationURL' => ['nullable', 'string'],
            'eventLogoText' => ['nullable', 'string'],
            'merchandiseURL' => ['nullable', 'string'],
            'orderFoodURL' => ['nullable', 'string'],
            'parkingInformationURL' => ['nullable', 'string'],
            'purchaseParkingURL' => ['nullable', 'string'],
            'sellURL' => ['nullable', 'string'],
            'transferURL' => ['nullable', 'string'],
            'transitInformationURL' => ['nullable', 'string'],
        ]);
    }
}
