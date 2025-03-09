<?php

namespace Spatie\LaravelMobilePass;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelMobilePass\Commands\LaravelMobilePassCommand;

class LaravelMobilePassServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-mobile-pass')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_mobile_pass_table');
    }
}
