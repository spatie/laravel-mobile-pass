<?php

namespace Spatie\LaravelMobilePass\Enums;

enum DataDetectorType: string
{
    case PhoneNumber = 'PKDataDetectorTypePhoneNumber';
    case Link = 'PKDataDetectorTypeLink';
    case Address = 'PKDataDetectorTypeAddress';
    case CalendarEvent = 'PKDataDetectorTypeCalendarEvent';
}
