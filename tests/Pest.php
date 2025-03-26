<?php

use Dotenv\Dotenv;
use Illuminate\Support\Arr;
use Spatie\LaravelMobilePass\Support\PkPassReader;
use Spatie\LaravelMobilePass\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = Dotenv::createImmutable(__DIR__.'/..');

    $dotEnv->load();
}

function getTestSupportPath(string $path): string
{
    return __DIR__.'/TestSupport/'.$path;
}

function tempPath(string $path = ''): string
{
    return test()->temporaryDirectory->path($path);
}

expect()->extend('toMatchMobilePassSnapshot', function () {
    storePassInTemporaryDirectory($this->value);

    $passkeyReader = PkPassReader::fromString($this->value);

    $this->value = $passkeyReader->toArray();

    $this->value = removeRandomMobilePassValues($this->value);

    return $this->toMatchSnapshot();
});

function removeRandomMobilePassValues(array $values): array
{
    $replacementString = '<random>';

    foreach ($values['manifest'] ?? [] as $key => $value) {
        $values['manifest'][$key] = $replacementString;
    }

    Arr::set($values, 'pass.serialNumber', $replacementString);

    return $values;
}

function storePassInTemporaryDirectory(string $passkeyContent): void
{
    $extension = '.pkpass';

    $basePath = tempPath().'/generated-passkeys/'.test()->name().'/passkey';

    $temporaryPath = $basePath;

    $suffix = 2;

    while (file_exists($temporaryPath.$extension)) {
        $temporaryPath = $basePath.'-'.$suffix;
        $suffix++;
    }

    $fullPath = $temporaryPath.$extension;

    if (! file_exists(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0777, true);
    }

    file_put_contents($fullPath, $passkeyContent);
}
