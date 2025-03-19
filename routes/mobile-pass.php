<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelMobilePass\Http\Controllers\CheckForUpdatesController;
use Spatie\LaravelMobilePass\Http\Controllers\GetAssociatedSerialsForDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\LogController;
use Spatie\LaravelMobilePass\Http\Controllers\RegisterDeviceController;
use Spatie\LaravelMobilePass\Http\Controllers\UnregisterDeviceController;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyPasskitRequest;

Route::group([
    'prefix' => 'passkit/v1',
], function ($router) {
    $router
        ->post('/devices/{deviceId}/registrations/{passTypeId}/{passSerial}', RegisterDeviceController::class)
        ->middleware(VerifyPasskitRequest::class);

    $router
        ->get('/passes/{passTypeId}/{passSerial}', CheckForUpdatesController::class);
    // ->middleware(VerifyPasskitRequest::class);

    $router
        ->delete('/devices/{deviceId}/registrations/{passTypeId}/{passSerial}', UnregisterDeviceController::class)
        ->middleware(VerifyPasskitRequest::class);

    // According to Apple's docs, these endpoints should _not_ be authenticated.
    $router->get('/devices/{deviceId}/registrations/{passTypeId}', GetAssociatedSerialsForDeviceController::class);
    $router->post('/log', LogController::class);
});
