<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;

it('builds a basic field content', function () {
    $pass = FieldContent::make(key: 'some-key');

    expect($pass->toArray())->toBe(['key' => 'some-key']);
});

it('builds a field content with a label and value', function () {
    $pass = FieldContent::make(key: 'some-key')
        ->withLabel('My label')
        ->withValue('My value');

    expect($pass->toArray())->toBe([
        'key' => 'some-key',
        'label' => 'My label',
        'value' => 'My value',
    ]);
});

it('translates the :value placeholder to Apple\'s %@ format in change messages', function () {
    $pass = FieldContent::make(key: 'seat')
        ->showMessageWhenChanged('Your seat has changed to :value');

    expect($pass->toArray())->toBe([
        'key' => 'seat',
        'changeMessage' => 'Your seat has changed to %@',
    ]);
});
