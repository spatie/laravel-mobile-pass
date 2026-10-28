<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the loyalty object to Google', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    $pass = LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setAccountId('AC-42')
        ->setAccountName('Jane Doe')
        ->setBalanceString('125 points')
        ->setBarcode(BarcodeType::Aztec, 'LP-42')
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.jane');
    expect($pass->content['googleClassId'])->toBe('3388.lp-2026');
    expect($pass->content['googleClassType'])->toBe('loyaltyClass');

    Http::assertSent(function ($request) {
        expect($request['accountId'])->toBe('AC-42');
        expect($request['accountName'])->toBe('Jane Doe');
        expect($request['loyaltyPoints']['balance'])->toBe(['string' => '125 points']);
        expect($request['barcode']['type'])->toBe('AZTEC');

        return true;
    });
});

it('sends an int balance', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceInt(1250)
        ->setSecondaryBalanceInt(3)
        ->save();

    Http::assertSent(function ($request) {
        expect($request['loyaltyPoints']['balance'])->toBe(['int' => 1250]);
        expect($request['secondaryLoyaltyPoints']['balance'])->toBe(['int' => 3]);

        return true;
    });
});

it('sends a money balance as micros with a currency code', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceMoney(12_500_000, 'EUR')
        ->setSecondaryBalanceMoney(500_000, 'USD')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['loyaltyPoints']['balance'])->toBe([
            'money' => ['micros' => '12500000', 'currencyCode' => 'EUR'],
        ]);
        expect($request['secondaryLoyaltyPoints']['balance'])->toBe([
            'money' => ['micros' => '500000', 'currencyCode' => 'USD'],
        ]);

        return true;
    });
});

it('maps the deprecated setBalanceMicros() onto a single valid balance subtype', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceMicros(1250)
        ->setSecondaryBalanceMicros(2_000_000, 'EUR')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['loyaltyPoints']['balance'])->toBe(['int' => 1250]);
        expect($request['secondaryLoyaltyPoints']['balance'])->toBe([
            'money' => ['micros' => '2000000', 'currencyCode' => 'EUR'],
        ]);

        return true;
    });
});

it('only ever sends one balance subtype, the last one set', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceMicros(125000000)
        ->setBalanceString('125 points')
        ->setSecondaryBalanceString('Gold')
        ->setSecondaryBalanceInt(7)
        ->save();

    Http::assertSent(function ($request) {
        expect($request['loyaltyPoints']['balance'])->toBe(['string' => '125 points']);
        expect($request['secondaryLoyaltyPoints']['balance'])->toBe(['int' => 7]);

        return true;
    });
});

it('compiles points labels and secondary loyalty points', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceString('125')
        ->setBalanceLabel('Points')
        ->setSecondaryBalanceString('Gold')
        ->setSecondaryBalanceLabel('Tier')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['loyaltyPoints']['label'])->toBe('Points');
        expect($request['loyaltyPoints']['balance']['string'])->toBe('125');
        expect($request['secondaryLoyaltyPoints']['label'])->toBe('Tier');
        expect($request['secondaryLoyaltyPoints']['balance']['string'])->toBe('Gold');

        return true;
    });
});

it('omits secondaryLoyaltyPoints when no secondary balance is set', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setBalanceString('125')
        ->save();

    Http::assertSent(function ($request) {
        expect($request->data())->not->toHaveKey('secondaryLoyaltyPoints');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    LoyaltyPassBuilder::make()->setAccountId('AC-42')->save();
})->throws(RuntimeException::class);
