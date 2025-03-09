<?php

namespace Spatie\LaravelMobilePass\Enums;

enum DateType: string {
    case None = 'PKDateStyleNone';
    case Short = 'PKDateStyleShort';
    case Medium = 'PKDateStyleMedium';
    case Long = 'PKDateStyleLong';
    case Full = 'PKDateStyleFull';
}
