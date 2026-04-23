<?php

namespace Spatie\LaravelMobilePass\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelMobilePass\MobilePassServiceProvider;
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
            MobilePassServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mobile-pass.apple.certificate', null);
        $app['config']->set('mobile-pass.apple.certificate_path', __DIR__.'/TestSupport/apple/test-certificate.p12');
        $app['config']->set('mobile-pass.apple.certificate_password', 'test');
        $app['config']->set('mobile-pass.apple.type_identifier', 'pass.com.example');
        $app['config']->set('mobile-pass.apple.team_identifier', 'ABCD1234EF');
        $app['config']->set('mobile-pass.apple.organization_name', 'Spatie Tests');

        Schema::dropAllTables();

        $migration = include __DIR__.'/../database/migrations/create_mobile_pass_tables.php.stub';
        $migration->up();

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
