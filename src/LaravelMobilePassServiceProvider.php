<?php

namespace Spatie\LaravelMobilePass;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMobilePassServiceProvider extends PackageServiceProvider
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
