<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PassengerCapability: string
{
    case Preboarding = 'PKPassengerCapabilityPreboarding';
    case PriorityBoarding = 'PKPassengerCapabilityPriorityBoarding';
    case Carryon = 'PKPassengerCapabilityCarryon';
    case PersonalItem = 'PKPassengerCapabilityPersonalItem';
    case LapInfant = 'PKPassengerCapabilityLapInfant';
}
