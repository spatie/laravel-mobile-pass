<?php

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\RelevantDate;

it('serializes a single relevant date', function () {
    $relevantDate = RelevantDate::make(date: Carbon::parse('1965-08-15T20:00:00-04:00'));

    expect($relevantDate->toArray())->toBe([
        'date' => '1965-08-15T20:00:00-04:00',
    ]);
});

it('serializes a relevant date interval', function () {
    $relevantDate = RelevantDate::make(
        startDate: Carbon::parse('1965-08-15T18:00:00-04:00'),
        endDate: Carbon::parse('1965-08-15T23:00:00-04:00'),
    );

    expect($relevantDate->toArray())->toBe([
        'startDate' => '1965-08-15T18:00:00-04:00',
        'endDate' => '1965-08-15T23:00:00-04:00',
    ]);
});

it('hydrates from an array', function () {
    $relevantDate = RelevantDate::fromArray([
        'startDate' => '1965-08-15T18:00:00-04:00',
        'endDate' => '1965-08-15T23:00:00-04:00',
    ]);

    expect($relevantDate->date)->toBeNull();
    expect($relevantDate->startDate->toIso8601String())->toBe('1965-08-15T18:00:00-04:00');
    expect($relevantDate->endDate->toIso8601String())->toBe('1965-08-15T23:00:00-04:00');
});
