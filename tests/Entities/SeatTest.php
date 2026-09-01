<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

it('serializes every property to its apple wire key', function () {
    $seat = Seat::make(
        description: 'Aisle seat with extra legroom',
        identifier: 'SEAT-22',
        number: '22',
        row: '8',
        section: 'B12',
        type: 'Reserved',
        aisle: 'A',
        level: 'Lower Bowl',
        sectionColor: 'rgb(23,187,82)',
    );

    expect($seat->toArray())->toBe([
        'seatAisle' => 'A',
        'seatDescription' => 'Aisle seat with extra legroom',
        'seatIdentifier' => 'SEAT-22',
        'seatLevel' => 'Lower Bowl',
        'seatNumber' => '22',
        'seatRow' => '8',
        'seatSection' => 'B12',
        'seatSectionColor' => 'rgb(23,187,82)',
        'seatType' => 'Reserved',
    ]);
});

it('round-trips through the apple wire keys', function () {
    $values = [
        'seatAisle' => 'A',
        'seatDescription' => 'Aisle seat with extra legroom',
        'seatIdentifier' => 'SEAT-22',
        'seatLevel' => 'Lower Bowl',
        'seatNumber' => '22',
        'seatRow' => '8',
        'seatSection' => 'B12',
        'seatSectionColor' => 'rgb(23,187,82)',
        'seatType' => 'Reserved',
    ];

    expect(Seat::fromArray($values)->toArray())->toBe($values);
});

it('hydrates the bare keys written before the wire keys were corrected', function () {
    $seat = Seat::fromArray([
        'description' => 'Aisle seat with extra legroom',
        'identifier' => 'SEAT-22',
        'number' => '22',
        'row' => '8',
        'section' => 'B12',
        'type' => 'Reserved',
    ]);

    expect($seat->toArray())->toBe([
        'seatDescription' => 'Aisle seat with extra legroom',
        'seatIdentifier' => 'SEAT-22',
        'seatNumber' => '22',
        'seatRow' => '8',
        'seatSection' => 'B12',
        'seatType' => 'Reserved',
    ]);
});

it('prefers the wire key when a bare key is present alongside it', function () {
    $seat = Seat::fromArray([
        'seatNumber' => '22',
        'number' => '99',
    ]);

    expect($seat->number)->toBe('22');
});

it('keeps the original positional argument order', function () {
    $seat = new Seat('Aisle seat with extra legroom', 'SEAT-22', '22', '8', 'B12', 'Reserved');

    expect($seat->description)->toBe('Aisle seat with extra legroom');
    expect($seat->identifier)->toBe('SEAT-22');
    expect($seat->number)->toBe('22');
    expect($seat->row)->toBe('8');
    expect($seat->section)->toBe('B12');
    expect($seat->type)->toBe('Reserved');
    expect($seat->aisle)->toBeNull();
    expect($seat->level)->toBeNull();
    expect($seat->sectionColor)->toBeNull();
});
