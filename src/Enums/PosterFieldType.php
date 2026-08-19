<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PosterFieldType: string
{
    case Header = 'posterHeaderFields';
    case Primary = 'posterPrimaryFields';
    case Footer = 'posterFooterFields';
    case Back = 'posterBackFields';
}
