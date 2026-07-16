<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;
use Spatie\LaravelMobilePass\Builders\Google\OfferPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

// --- Hydration ---

it('hydrateLocalizedString restores translatedValues via all()', function () {
    Http::fake(['*/eventTicketClass*' => Http::response([
        'resources' => [[
            'id' => '3388.a',
            'eventName' => [
                'defaultValue' => ['language' => 'en-US', 'value' => 'Rock Concert'],
                'translatedValues' => [
                    ['language' => 'it', 'value' => 'Concerto Rock'],
                    ['language' => 'fr', 'value' => 'Concert Rock'],
                ],
            ],
        ]],
    ], 200)]);

    $classes = EventTicketPassClass::all();
    $class = $classes[0];

    expect($class->getEventName())->toBe('Rock Concert');

    // Re-save and assert translatedValues are forwarded to the API
    Http::fake(['*/eventTicketClass/3388.a' => Http::response([], 200)]);
    $class->save();

    Http::assertSent(function ($request) {
        expect($request['eventName']['translatedValues'])
            ->toHaveCount(2)
            ->toContain(['language' => 'it', 'value' => 'Concerto Rock']);

        return true;
    });
});

// --- Setter: accepts LocalizedString ---

it('EventTicketPassClass::setEventName accepts a LocalizedString with translations', function () {
    Http::fake(['*/eventTicketClass' => Http::response([], 200)]);

    EventTicketPassClass::make('ts-001')
        ->setEventName(
            LocalizedString::of('Rock Concert', 'en-US')
                ->addTranslation('it', 'Concerto Rock')
        )
        ->save();

    Http::assertSent(function ($request) {
        expect($request['eventName']['defaultValue']['value'])->toBe('Rock Concert');
        expect($request['eventName']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Concerto Rock']);

        return true;
    });
});

it('GenericPassClass::setCardTitle accepts a LocalizedString with translations', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('gc-001')
        ->setCardTitle(
            LocalizedString::of('My Card', 'en-US')
                ->addTranslation('it', 'La Mia Carta')
        )
        ->save();

    Http::assertSent(function ($request) {
        expect($request['cardTitle']['defaultValue']['value'])->toBe('My Card');
        expect($request['cardTitle']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'La Mia Carta']);

        return true;
    });
});

it('EventTicketPassBuilder::setSection accepts a LocalizedString with translations', function () {
    Http::fake(['*/eventTicketObject' => Http::response([], 200)]);

    EventTicketPassBuilder::make()
        ->setClass('ts-001')
        ->setSection(
            LocalizedString::of('Floor', 'en-US')
                ->addTranslation('it', 'Platea')
        )
        ->save();

    Http::assertSent(function ($request) {
        expect($request['seatInfo']['section']['defaultValue']['value'])->toBe('Floor');
        expect($request['seatInfo']['section']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Platea']);

        return true;
    });
});

// --- Throw guard ---

it('throws when LocalizedString and non-default language are both passed to setEventName', function () {
    EventTicketPassClass::make('ts-001')
        ->setEventName(LocalizedString::of('Rock Concert', 'en-US'), 'it');
})->throws(InvalidArgumentException::class);

it('throws when LocalizedString and non-default language are both passed to setCardTitle', function () {
    GenericPassClass::make('gc-001')
        ->setCardTitle(LocalizedString::of('My Card', 'en-US'), 'it');
})->throws(InvalidArgumentException::class);

it('throws when LocalizedString and non-default language are both passed to setSection', function () {
    EventTicketPassBuilder::make()
        ->setSection(LocalizedString::of('Floor', 'en-US'), 'it');
})->throws(InvalidArgumentException::class);

// --- Backward compat ---

it('plain string still works on setEventName', function () {
    Http::fake(['*/eventTicketClass' => Http::response([], 200)]);

    EventTicketPassClass::make('ts-001')
        ->setEventName('Rock Concert')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['eventName']['defaultValue']['value'])->toBe('Rock Concert');
        expect($request['eventName']['defaultValue']['language'])->toBe('en-US');
        expect($request['eventName'])->not->toHaveKey('translatedValues');

        return true;
    });
});

// --- LoyaltyPassClass ---

it('LoyaltyPassClass::setProgramName accepts a LocalizedString with translations', function () {
    Http::fake(['*/loyaltyClass' => Http::response([], 200)]);

    LoyaltyPassClass::make('spatie-club')
        ->setProgramName(
            LocalizedString::of('Spatie Club', 'en-US')
                ->addTranslation('it', 'Club Spatie')
        )
        ->save();

    Http::assertSent(function ($request) {
        expect($request['programName'])->toBe('Spatie Club');
        expect($request['localizedProgramName']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Club Spatie']);

        return true;
    });
});

it('LoyaltyPassClass plain string backward compat', function () {
    Http::fake(['*/loyaltyClass' => Http::response([], 200)]);

    LoyaltyPassClass::make('spatie-club')
        ->setProgramName('Spatie Club')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['programName'])->toBe('Spatie Club');
        expect($request['localizedProgramName']['defaultValue']['value'])->toBe('Spatie Club');
        expect($request['localizedProgramName'])->not->toHaveKey('translatedValues');

        return true;
    });
});

it('LoyaltyPassClass hydration restores programName translations', function () {
    Http::fake(['*/loyaltyClass*' => Http::response([
        'resources' => [[
            'id' => '3388.spatie-club',
            'programName' => 'Spatie Club',
            'localizedProgramName' => [
                'defaultValue' => ['language' => 'en-US', 'value' => 'Spatie Club'],
                'translatedValues' => [['language' => 'it', 'value' => 'Club Spatie']],
            ],
        ]],
    ], 200)]);

    $classes = LoyaltyPassClass::all();

    expect($classes[0]->getProgramName())->toBe('Spatie Club');

    Http::fake(['*/loyaltyClass/3388.spatie-club' => Http::response([], 200)]);
    $classes[0]->save();

    Http::assertSent(function ($request) {
        expect($request['programName'])->toBe('Spatie Club');
        expect($request['localizedProgramName']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Club Spatie']);

        return true;
    });
});

it('throws when LocalizedString and non-default language are both passed to setProgramName', function () {
    LoyaltyPassClass::make('spatie-club')
        ->setProgramName(LocalizedString::of('Spatie Club', 'en-US'), 'it');
})->throws(InvalidArgumentException::class);

// --- OfferPassClass ---

it('OfferPassClass::setTitle accepts a LocalizedString with translations', function () {
    Http::fake(['*/offerClass' => Http::response([], 200)]);

    OfferPassClass::make('summer-sale')
        ->setTitle(
            LocalizedString::of('Summer Sale', 'en-US')
                ->addTranslation('it', 'Saldi Estivi')
        )
        ->save();

    Http::assertSent(function ($request) {
        expect($request['title'])->toBe('Summer Sale');
        expect($request['localizedTitle']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Saldi Estivi']);

        return true;
    });
});

it('OfferPassClass plain string backward compat', function () {
    Http::fake(['*/offerClass' => Http::response([], 200)]);

    OfferPassClass::make('summer-sale')
        ->setTitle('Summer Sale')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['title'])->toBe('Summer Sale');
        expect($request['localizedTitle']['defaultValue']['value'])->toBe('Summer Sale');
        expect($request['localizedTitle'])->not->toHaveKey('translatedValues');

        return true;
    });
});

it('OfferPassClass hydration restores title translations', function () {
    Http::fake(['*/offerClass*' => Http::response([
        'resources' => [[
            'id' => '3388.summer-sale',
            'title' => 'Summer Sale',
            'localizedTitle' => [
                'defaultValue' => ['language' => 'en-US', 'value' => 'Summer Sale'],
                'translatedValues' => [['language' => 'it', 'value' => 'Saldi Estivi']],
            ],
        ]],
    ], 200)]);

    $classes = OfferPassClass::all();

    expect($classes[0]->getTitle())->toBe('Summer Sale');

    Http::fake(['*/offerClass/3388.summer-sale' => Http::response([], 200)]);
    $classes[0]->save();

    Http::assertSent(function ($request) {
        expect($request['title'])->toBe('Summer Sale');
        expect($request['localizedTitle']['translatedValues'])
            ->toContain(['language' => 'it', 'value' => 'Saldi Estivi']);

        return true;
    });
});

it('throws when LocalizedString and non-default language are both passed to setTitle', function () {
    OfferPassClass::make('summer-sale')
        ->setTitle(LocalizedString::of('Summer Sale', 'en-US'), 'it');
})->throws(InvalidArgumentException::class);
