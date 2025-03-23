<?php

use Spatie\LaravelMobilePass\Entities\Image;

it('builds a basic Image entity', function () {
    $image = Image::make(
        getTestSupportPath('images/spatie-thumbnail.png')
    );

    expect($image->x1Path)->toBe(getTestSupportPath('images/spatie-thumbnail.png'));
    expect($image->x2Path)->toBeNull();
    expect($image->x3Path)->toBeNull();
});

it('throws an exception when the image does not exist', function () {
    Image::make(
        getTestSupportPath('images/non-existing.png')
    );
})->throws(InvalidArgumentException::class, 'File not found at path: ' . getTestSupportPath('images/non-existing.png'));