<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the object to Google', function () {
    Http::fake(['*/eventTicketObject' => Http::response([], 200)]);

    $pass = EventTicketPassBuilder::make()
        ->setClass('ts-2026')
        ->setObjectSuffix('john')
        ->setAttendeeName('John Smith')
        ->setSection('B12')
        ->setRow('8')
        ->setSeat('22')
        ->setBarcode(BarcodeType::Qr, 'TS-JS')
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.john');
    expect($pass->content['googleClassId'])->toBe('3388.ts-2026');
    expect($pass->content['googleClassType'])->toBe('eventTicketClass');

    Http::assertSent(function ($request) {
        expect($request['classId'])->toBe('3388.ts-2026');
        expect($request['id'])->toBe('3388.john');
        expect($request['ticketHolderName'])->toBe('John Smith');
        expect($request['seatInfo']['section'])->toBe('B12');
        expect($request['seatInfo']['row'])->toBe('8');
        expect($request['seatInfo']['seat'])->toBe('22');
        expect($request['barcode']['type'])->toBe('QR_CODE');
        expect($request['barcode']['value'])->toBe('TS-JS');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    EventTicketPassBuilder::make()->setAttendeeName('John')->save();
})->throws(RuntimeException::class);
