<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

it('throws a clear exception when hydrating an unrecognized barcode format', function () {
    Barcode::fromArray([
        'format' => 'PKBarcodeFormatFutureFormat',
        'message' => 'TICKET-12345',
        'messageEncoding' => 'iso-8859-1',
    ]);
})->throws(InvalidConfig::class, 'PKBarcodeFormatFutureFormat');
