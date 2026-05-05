<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Exceptions\ImageNotFound;

it('builds a basic Image entity', function () {
    $image = Image::make(
        getTestSupportPath('images/spatie-thumbnail.png')
    );

    expect($image->x1Path)->toBe(getTestSupportPath('images/spatie-thumbnail.png'));
    expect($image->x2Path)->toBeNull();
    expect($image->x3Path)->toBeNull();
    expect($image->isRemote)->toBeFalse();
});

it('throws an exception when the image does not exist', function () {
    Image::make(
        getTestSupportPath('images/non-existing.png')
    );
})->throws(ImageNotFound::class, 'No image file found at path `'.getTestSupportPath('images/non-existing.png').'`.');

it('allows building a remote Image without local files', function () {
    $image = Image::makeRemote(
        'https://example.com/pass/icon.png',
        'https://example.com/pass/icon@2x.png',
    );

    expect($image->x1Path)->toBe('https://example.com/pass/icon.png')
        ->and($image->x2Path)->toBe('https://example.com/pass/icon@2x.png')
        ->and($image->x3Path)->toBeNull()
        ->and($image->isRemote)->toBeTrue();
});
