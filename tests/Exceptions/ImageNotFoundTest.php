<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Exceptions\ImageNotFound;

it('throws ImageNotFound when the file does not exist', function () {
    Image::make('/tmp/definitely-not-here-'.uniqid().'.png');
})->throws(ImageNotFound::class);

it('mentions the missing path in the message', function () {
    $missing = '/tmp/definitely-not-here-'.uniqid().'.png';

    try {
        Image::make($missing);

        $this->fail('Expected ImageNotFound to be thrown.');
    } catch (ImageNotFound $exception) {
        expect($exception->getMessage())->toContain($missing);
    }
});
