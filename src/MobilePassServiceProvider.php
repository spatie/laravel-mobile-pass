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
            ->hasMigrations([
                'create_mobile_pass_tables',
                'change_mobile_pass_id_to_string',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(GoogleJwtSigner::class);
        $this->app->singleton(GoogleWalletClient::class);
    }
}
