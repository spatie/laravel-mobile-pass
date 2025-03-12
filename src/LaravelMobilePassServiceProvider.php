<?php

namespace Spatie\LaravelMobilePass;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMobilePassServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-mobile-pass')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_mobile_pass_table');
    }
}
