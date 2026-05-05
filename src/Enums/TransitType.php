<?php

namespace Spatie\LaravelMobilePass\Enums;

enum TransitType: string
{
    case Air = 'PKTransitTypeAir';
    case Boat = 'PKTransitTypeBoat';
    case Bus = 'PKTransitTypeBus';
    case Generic = 'PKTransitTypeGeneric';
    case Train = 'PKTransitTypeTrain';
}
