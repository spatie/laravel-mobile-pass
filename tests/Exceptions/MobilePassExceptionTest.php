<?php

use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Exceptions\AppleWalletRequestFailed;
use Spatie\LaravelMobilePass\Exceptions\CannotDownload;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletRequestFailed;
use Spatie\LaravelMobilePass\Exceptions\ImageNotFound;
use Spatie\LaravelMobilePass\Exceptions\InvalidCertificate;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;
use Spatie\LaravelMobilePass\Exceptions\MobilePassException;
use Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('lets you catch every package exception through the MobilePassException interface', function (string $exceptionClass) {
    expect(is_subclass_of($exceptionClass, MobilePassException::class))->toBeTrue();
})->with([
    AppleWalletRequestFailed::class,
    CannotDownload::class,
    GoogleWalletRequestFailed::class,
    ImageNotFound::class,
    InvalidCertificate::class,
    InvalidConfig::class,
    InvalidPass::class,
    PlatformDoesntSupport::class,
]);

it('can be caught generically', function () {
    $pass = MobilePass::factory()->make(['platform' => Platform::Google]);

    try {
        throw CannotDownload::wrongPlatform($pass);
    } catch (MobilePassException $exception) {
        expect($exception)->toBeInstanceOf(CannotDownload::class);
    }
});
