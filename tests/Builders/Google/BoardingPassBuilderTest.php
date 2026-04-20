<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the boarding object to Google', function () {
    Http::fake(['*/flightObject' => Http::response([], 200)]);

    $pass = BoardingPassBuilder::make()
        ->setClass('ba-2026')
        ->setObjectSuffix('jane')
        ->setPassengerName('Jane Doe')
        ->setSeatNumber('12A')
        ->setConfirmationCode('XYZ123')
        ->setBarcode(Barcode::make(BarcodeType::PDF417, 'BOARDING-CODE'))
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.jane');
    expect($pass->content['googleClassId'])->toBe('3388.ba-2026');
    expect($pass->content['googleClassType'])->toBe('flightClass');

    Http::assertSent(function ($request) {
        expect($request['classId'])->toBe('3388.ba-2026');
        expect($request['id'])->toBe('3388.jane');
        expect($request['passengerName'])->toBe('Jane Doe');
        expect($request['boardingAndSeatingInfo']['seatNumber'])->toBe('12A');
        expect($request['reservationInfo']['confirmationCode'])->toBe('XYZ123');
        expect($request['barcode']['type'])->toBe('PDF_417');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    BoardingPassBuilder::make()->setPassengerName('Jane')->save();
})->throws(RuntimeException::class);
