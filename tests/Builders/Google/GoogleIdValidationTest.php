<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

dataset('invalid suffixes', [
    'empty' => [''],
    'slash' => ['foo/bar'],
    'path traversal' => ['../genericClass'],
    'query string' => ['foo?issuerId=1'],
    'space' => ['foo bar'],
    'hash' => ['foo#bar'],
]);

it('accepts suffixes made of letters, digits, dots, underscores and dashes', function () {
    expect(GenericPassClass::make('Abc.123_x-y')->id())->toBe('3388.Abc.123_x-y');
    expect(EventTicketPassBuilder::make()->setClass('Abc.123_x-y')->setObjectSuffix('o.1_2-3')->objectId())
        ->toBe('3388.o.1_2-3');
});

it('rejects invalid class suffixes on make()', function (string $suffix) {
    GenericPassClass::make($suffix);
})->with('invalid suffixes')->throws(InvalidPass::class);

it('rejects invalid class suffixes on find() without calling Google', function (string $suffix) {
    Http::fake();

    expect(fn () => GenericPassClass::find($suffix))->toThrow(InvalidPass::class);

    Http::assertNothingSent();
})->with('invalid suffixes');

it('rejects invalid suffixes on setClass()', function (string $suffix) {
    EventTicketPassBuilder::make()->setClass($suffix);
})->with('invalid suffixes')->throws(InvalidPass::class);

it('rejects invalid suffixes on setObjectSuffix()', function (string $suffix) {
    EventTicketPassBuilder::make()->setObjectSuffix($suffix);
})->with('invalid suffixes')->throws(InvalidPass::class);

it('requires a configured issuer id for class ids', function () {
    config()->set('mobile-pass.google.issuer_id', '');

    EventTicketPassClass::make('ts-2026')->id();
})->throws(InvalidConfig::class);

it('requires a configured issuer id before finding a class', function () {
    config()->set('mobile-pass.google.issuer_id', null);
    Http::fake();

    expect(fn () => EventTicketPassClass::find('ts-2026'))->toThrow(InvalidConfig::class);

    Http::assertNothingSent();
});

it('requires a configured issuer id for object ids', function () {
    config()->set('mobile-pass.google.issuer_id', '');

    EventTicketPassBuilder::make()->setClass('ts-2026')->objectId();
})->throws(InvalidConfig::class);

it('requires a configured issuer id for class ids on objects', function () {
    config()->set('mobile-pass.google.issuer_id', '');

    EventTicketPassBuilder::make()->setClass('ts-2026')->classId();
})->throws(InvalidConfig::class);
