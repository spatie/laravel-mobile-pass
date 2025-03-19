<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PassType: string
{
    case BoardingPass = 'boardingPass';
    case Coupon = 'coupon';
    case EventTicket = 'eventTicket';
    case StoreCard = 'storeCard';
    case Generic = 'generic';
}
