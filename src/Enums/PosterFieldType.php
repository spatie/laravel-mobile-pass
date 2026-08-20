<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PosterFieldType: string
{
    case Header = 'posterHeaderFields';
    case Primary = 'posterPrimaryFields';
    case Footer = 'posterFooterFields';
    case Back = 'posterBackFields';

    public function jsonKey(): string
    {
        return match ($this) {
            self::Header => 'headerFields',
            self::Primary => 'primaryFields',
            self::Footer => 'footerFields',
            self::Back => 'backFields',
        };
    }
}
