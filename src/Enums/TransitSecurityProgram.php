<?php

namespace Spatie\LaravelMobilePass\Enums;

enum TransitSecurityProgram: string
{
    case TsaPreCheck = 'PKTransitSecurityProgramTSAPreCheck';
    case TsaPreCheckTouchlessId = 'PKTransitSecurityProgramTSAPreCheckTouchlessID';
    case Oss = 'PKTransitSecurityProgramOSS';
    case Iti = 'PKTransitSecurityProgramITI';
    case Itd = 'PKTransitSecurityProgramITD';
    case GlobalEntry = 'PKTransitSecurityProgramGlobalEntry';
    case Clear = 'PKTransitSecurityProgramCLEAR';
}
