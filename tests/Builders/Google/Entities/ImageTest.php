<?php

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;

it('wraps an https URL verbatim', function () {
    $image = Image::fromUrl('https://cdn.example.com/logo.png');

    expect($image->publicUrl())->toBe('https://cdn.example.com/logo.png');
    expect($image->toArray())->toBe([
        'sourceUri' => ['uri' => 'https://cdn.example.com/logo.png'],
    ]);
});

it('captures a local path for later hosting', function () {
    $image = Image::fromLocalPath(__DIR__.'/../../../TestSupport/images/spatie-thumbnail.png');

    expect($image->localPath)->not()->toBeNull();
    expect($image->url)->toBeNull();
});
