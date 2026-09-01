<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\RelevantDate;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

it('serialises a single moment to both the iOS 18 and iOS 26 keys', function () {
    $relevantDate = RelevantDate::forDate(Carbon::parse('1965-08-15T20:00:00-04:00'));

    expect($relevantDate->toArray())->toBe([
        'relevantDate' => '1965-08-15T20:00:00-04:00',
        'date' => '1965-08-15T20:00:00-04:00',
    ]);
});

it('serialises a relevant date interval', function () {
    $relevantDate = RelevantDate::forInterval(
        startDate: Carbon::parse('1965-08-15T18:00:00-04:00'),
        endDate: Carbon::parse('1965-08-15T23:00:00-04:00'),
    );

    expect($relevantDate->toArray())->toBe([
        'startDate' => '1965-08-15T18:00:00-04:00',
        'endDate' => '1965-08-15T23:00:00-04:00',
    ]);
});

it('hydrates an interval from an array', function () {
    $relevantDate = RelevantDate::fromArray([
        'startDate' => '1965-08-15T18:00:00-04:00',
        'endDate' => '1965-08-15T23:00:00-04:00',
    ]);

    expect($relevantDate->date)->toBeNull();
    expect($relevantDate->startDate->toIso8601String())->toBe('1965-08-15T18:00:00-04:00');
    expect($relevantDate->endDate->toIso8601String())->toBe('1965-08-15T23:00:00-04:00');
});

it('hydrates a single moment from either the date or the relevantDate key', function (string $key) {
    $relevantDate = RelevantDate::fromArray([$key => '1965-08-15T20:00:00-04:00']);

    expect($relevantDate->date->toIso8601String())->toBe('1965-08-15T20:00:00-04:00');
    expect($relevantDate->startDate)->toBeNull();
    expect($relevantDate->endDate)->toBeNull();
})->with(['date', 'relevantDate']);

it('reports the moment an entry becomes relevant', function () {
    expect(RelevantDate::forDate(Carbon::parse('1965-08-15T20:00:00-04:00'))->moment()->toIso8601String())
        ->toBe('1965-08-15T20:00:00-04:00');

    expect(RelevantDate::forInterval(
        Carbon::parse('1965-08-15T18:00:00-04:00'),
        Carbon::parse('1965-08-15T23:00:00-04:00'),
    )->moment()->toIso8601String())->toBe('1965-08-15T18:00:00-04:00');
});

it('refuses to hydrate an interval that is missing its end date', function () {
    RelevantDate::fromArray(['startDate' => '1965-08-15T18:00:00-04:00']);
})->throws(InvalidConfig::class);

it('refuses to hydrate an entry with no dates at all', function () {
    RelevantDate::fromArray([]);
})->throws(InvalidConfig::class);
