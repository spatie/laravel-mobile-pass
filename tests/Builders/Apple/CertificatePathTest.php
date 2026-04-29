<?php

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;

afterEach(function () {
    foreach (glob(sys_get_temp_dir().'/LaravelMobilePass-*.p12') as $file) {
        @unlink($file);
    }
});

it('returns the configured certificate path when no inline certificate is set', function () {
    config()->set('mobile-pass.apple.certificate', null);
    config()->set('mobile-pass.apple.certificate_path', '/path/to/cert.p12');

    expect(CouponPassBuilder::getCertificatePath())->toBe('/path/to/cert.p12');
});

it('writes a base64-encoded certificate to a hashed temp path', function () {
    $contents = base64_encode('certificate-contents');
    config()->set('mobile-pass.apple.certificate', $contents);

    $path = CouponPassBuilder::getCertificatePath();

    expect($path)->toBe(sys_get_temp_dir().'/LaravelMobilePass-'.md5($contents).'.p12')
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toBe('certificate-contents');
});

it('writes a new file when the certificate is rotated', function () {
    $first = base64_encode('first-certificate');
    config()->set('mobile-pass.apple.certificate', $first);

    $firstPath = CouponPassBuilder::getCertificatePath();

    $second = base64_encode('second-certificate');
    config()->set('mobile-pass.apple.certificate', $second);

    $secondPath = CouponPassBuilder::getCertificatePath();

    expect($secondPath)->not->toBe($firstPath)
        ->and(file_get_contents($secondPath))->toBe('second-certificate')
        ->and(file_get_contents($firstPath))->toBe('first-certificate');
});
