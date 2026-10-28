<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Exceptions\ImageNotFound;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

function remoteImagePass(): GenericPassBuilder
{
    return GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber('remote-images')
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'));
}

it('downloads each remote image once so the manifest matches the bundled file', function () {
    Http::fake([
        'example.com/logo.png' => Http::sequence()
            ->push('first-response')
            ->push('second-response'),
    ]);

    $reader = PkPassReader::fromString(
        remoteImagePass()->setRemoteLogoImage('https://example.com/logo.png')->generate()
    );

    Http::assertSentCount(1);

    expect($reader->fileContent('logo.png'))->toBe('first-response')
        ->and($reader->manifestProperty('logo.png'))->toBe(sha1('first-response'));
});

it('downloads remote locale images once', function () {
    Http::fake(['example.com/logo-nl.png' => Http::response('dutch-logo')]);

    $reader = PkPassReader::fromString(
        remoteImagePass()->setRemoteLocaleLogoImage('nl', 'https://example.com/logo-nl.png')->generate()
    );

    Http::assertSentCount(1);

    expect($reader->fileContent('nl.lproj/logo.png'))->toBe('dutch-logo');
});

it('throws when a remote image cannot be downloaded', function () {
    Http::fake(['example.com/logo.png' => Http::response('', 404)]);

    remoteImagePass()->setRemoteLogoImage('https://example.com/logo.png')->generate();
})->throws(ImageNotFound::class, 'status 404');

it('rejects remote image URLs that are not http or https', function (string $url) {
    remoteImagePass()->setRemoteLogoImage($url);
})->with([
    'file' => 'file:///etc/passwd',
    'php filter' => 'php://filter/resource=/etc/passwd',
    'no scheme' => '/etc/passwd',
])->throws(ImageNotFound::class, 'http or https');
