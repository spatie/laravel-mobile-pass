<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PassType
{
    case BoardingPass;
    case Coupon;
    case EventTicket;
    case StoreCard;
    case Generic;
}
