<?php

namespace Spatie\LaravelMobilePass\Enums;

enum EventType: string {
    case Generic = 'PKEventTypeGeneric';
    case LivePerformance = 'PKEventTypeLivePerformance';
    case Movie = 'PKEventTypeMovie';
    case Sports = 'PKEventTypeSports';
    case Conference = 'PKEventTypeConference';
    case Convention = 'PKEventTypeConvention';
    case Workshop = 'PKEventTypeWorkshop';
    case SocialGathering = 'PKEventTypeSocialGathering';
}
