<?php

namespace Spatie\LaravelMobilePass\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelMobilePass\LaravelMobilePassServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefaultValuesInConfig();

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

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    protected function setDefaultValuesInConfig(): self
    {
        config()->set('mobile-pass', [
            'organisation_name' => 'Organisation name',
            'type_identifier' => 'Type identifier',
            'team_identifier' => 'Team identifier',
            'apple' => [
                'certificate_path' => 'dummy path',
                'certificate_contents' => 'dummy contents',
                'certificate_password' => 'dummy password',
            ],
        ]);

        return $this;
    }
}
