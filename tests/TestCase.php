<?php

namespace Spatie\LaravelMobilePass\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelMobilePass\LaravelMobilePassServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = new TemporaryDirectory(__DIR__.'/TestSupport/temp');

        $this->temporaryDirectory->empty();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LaravelMobilePass\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMobilePassServiceProvider::class,
        ];
    }
}
