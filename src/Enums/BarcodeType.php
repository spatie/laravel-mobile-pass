<?php

namespace Spatie\LaravelMobilePass\Enums;

enum BarcodeType: string
{
    case Qr = 'PKBarcodeFormatQR';
    case Pdf417 = 'PKBarcodeFormatPDF417';
    case Aztec = 'PKBarcodeFormatAztec';
    case Code128 = 'PKBarcodeFormatCode128';
    case Code39 = 'PKBarcodeFormatCode39';
    case Codabar = 'PKBarcodeFormatCodabar';
    case Ean13 = 'PKBarcodeFormatEAN13';
}
