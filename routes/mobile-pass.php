<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelMobilePass\Http\Controllers\CheckForUpdatesController;
use Spatie\LaravelMobilePass\Http\Controllers\GetAssociatedSerialsForDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\MobilePassLogController;
use Spatie\LaravelMobilePass\Http\Controllers\RegisterDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\UnregisterDeviceController;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyPasskitRequest;

Route::macro('mobilePass', function (string $prefix = '') {
    Route::prefix("{$prefix}/passkit/v1")->group(function () {

        Route::middleware(VerifyPasskitRequest::class)->group(function () {
            Route::post('devices/{deviceId}/registrations/{passTypeId}/{passSerial}', RegisterDeviceController::class)
                ->name('mobile-pass.register-device');

            Route::get('passes/{passTypeId}/{passSerial}', CheckForUpdatesController::class)
                ->name('mobile-pass.check-for-updates');

            Route::delete('devices/{deviceId}/registrations/{passTypeId}/{passSerial}', UnregisterDeviceController::class)
                ->name('mobile-pass.unregister-device');
        });

        Route::get('devices/{deviceId}/registrations/{passTypeId}', GetAssociatedSerialsForDeviceController::class)
            ->name('mobile-pass.get-associated-serials-for-device');

        Route::post('log', MobilePassLogController::class)
            ->name('mobile-pass.logs');
    });
});
