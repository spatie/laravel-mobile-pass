<?php

namespace Spatie\LaravelMobilePass;

use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;
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

    public function registeringPackage(): void
    {
        $this->app->singleton(GoogleJwtSigner::class);
        $this->app->singleton(GoogleWalletClient::class);
    }
}
