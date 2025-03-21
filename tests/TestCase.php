<?php

namespace Spatie\LaravelMobilePass\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelMobilePass\LaravelMobilePassServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TestTime\TestTime;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze('Y-m-d H:i:s', '2025-01-01 00:00:00');
        Http::preventStrayRequests();

        $this->temporaryDirectory = (new TemporaryDirectory(__DIR__.'/TestSupport/temp'))->empty();

        Route::mobilePass();

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

    protected function getEnvironmentSetUp($app)
    {
        Schema::dropAllTables();

        $migration = include __DIR__.'/../database/migrations/create_mobile_pass_tables.php.stub';
        $migration->up();
    }
}
