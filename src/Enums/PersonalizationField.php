<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PersonalizationField: string
{
    case Name = 'PKPassPersonalizationFieldName';
    case PostalCode = 'PKPassPersonalizationFieldPostalCode';
    case EmailAddress = 'PKPassPersonalizationFieldEmailAddress';
    case PhoneNumber = 'PKPassPersonalizationFieldPhoneNumber';
}
