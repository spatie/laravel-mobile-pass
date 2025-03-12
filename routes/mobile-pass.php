<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelMobilePass\Http\Controllers\CheckForUpdatesController;
use Spatie\LaravelMobilePass\Http\Controllers\GetAssociatedSerialsForDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\LogController;
use Spatie\LaravelMobilePass\Http\Controllers\RegisterDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\UnregisterDeviceController;
use Spatie\LaravelMobilePass\Http\Middleware\PasskitServerVerify;

Route::group([
    'prefix' => 'passkit/v1',
], function ($router) {
    $router
        ->post('/devices/{deviceId}/registrations/{passId}/{passSerial}', RegisterDeviceController::class)
        ->middleware(PasskitServerVerify::class);

    $router
        ->get('/passes/{passId}/{passSerial}', CheckForUpdatesController::class)
        ->middleware(PasskitServerVerify::class);

    $router
        ->delete('/devices/{deviceId}/registrations/{passId}/{passSerial}', UnregisterDeviceController::class)
        ->middleware(PasskitServerVerify::class);

    // According to Apple's docs, these endpoints should _not_ be authenticated.
    $router->get('/devices/{deviceId}/registrations/{passId}', GetAssociatedSerialsForDeviceController::class);
    $router->post('/log', LogController::class);
});
