<?php

namespace Spatie\LaravelMobilePass\Enums;

enum NumberStyleType: string {
    case Decimal = 'PKNumberStyleDecimal';
    case Percent = 'PKNumberStylePercent';
    case Scientific = 'PKNumberStyleScientific';
    case SpellOut = 'PKNumberStyleSpellOut';
}
