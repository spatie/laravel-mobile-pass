<?php

use Spatie\LaravelMobilePass\Entities\FieldContent;

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
