<?php

namespace Spatie\LaravelMobilePass\Enums;

/**
 * @internal
 */
enum PosterFieldType: string
{
    case Header = 'posterHeaderFields';
    case Primary = 'posterPrimaryFields';
    case Footer = 'posterFooterFields';
    case Back = 'posterBackFields';
}
