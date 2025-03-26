<?php

namespace Spatie\LaravelMobilePass;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MobilePassServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-mobile-pass')
            ->hasConfigFile()
            ->hasRoutes('mobile-pass')
            ->hasMigration('create_mobile_pass_tables');

    }
}
