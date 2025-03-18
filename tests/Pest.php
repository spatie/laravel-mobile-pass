<?php

use Dotenv\Dotenv;
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

function tempPath(string $path): string
{
    return test()->temporaryDirectory->path($path);
}
