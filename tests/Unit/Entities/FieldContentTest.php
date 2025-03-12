<?php

use Spatie\LaravelMobilePass\Entities\FieldContent;
use function PHPUnit\Framework\assertSame;

it('builds a basic field content', function () {
    $pass = FieldContent::make(key: 'some-key');

    assertSame(
        ['key' => 'some-key'],
        $pass->toArray(),
    );
});

it('builds a field content with a label and value', function () {
    $pass = FieldContent::make(key: 'some-key')
        ->withLabel('My label')
        ->withValue('My value');

    assertSame([
        'key' => 'some-key',
        'label' => 'My label',
        'value' => 'My value'
    ], $pass->toArray());
});
