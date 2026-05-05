<?php

namespace Spatie\LaravelMobilePass\Enums;

enum FieldType: string
{
    case Header = 'headerFields';
    case Primary = 'primaryFields';
    case Secondary = 'secondaryFields';
    case Auxiliary = 'auxiliaryFields';
    case Back = 'backFields';
}
