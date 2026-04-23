<?php

use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Exceptions\InvalidCertificate;

it('throws InvalidCertificate when the certificate path does not exist', function () {
    config()->set('mobile-pass.apple.certificate_path', '/tmp/not-a-real-certificate-'.uniqid().'.p12');
    config()->set('mobile-pass.apple.certificate_password', 'whatever');

    GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setSerialNumber('abc')
        ->setDescription('Hello')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->generate();
})->throws(InvalidCertificate::class);

it('mentions the env vars in the error message and keeps the original as previous', function () {
    config()->set('mobile-pass.apple.certificate_path', '/tmp/not-a-real-certificate-'.uniqid().'.p12');
    config()->set('mobile-pass.apple.certificate_password', 'whatever');

    try {
        GenericPassBuilder::make()
            ->setOrganizationName('Spatie')
            ->setSerialNumber('abc')
            ->setDescription('Hello')
            ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
            ->generate();

        $this->fail('Expected InvalidCertificate to be thrown.');
    } catch (InvalidCertificate $exception) {
        expect($exception->getMessage())->toContain('MOBILE_PASS_APPLE_CERTIFICATE_PATH');
        expect($exception->getMessage())->toContain('MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD');
        expect($exception->getPrevious())->toBeInstanceOf(PKPass\PKPassException::class);
    }
});
