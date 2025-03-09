<?php

namespace Spatie\LaravelMobilePass\Enums;

enum BarcodeType: string
{
    case QR = 'PKBarcodeFormatQR';
    case PDF417 = 'PKBarcodeFormatPDF417';
    case Aztec = 'PKBarcodeFormatAztec';
    case Code128 = 'PKBarcodeFormatCode128';
}
