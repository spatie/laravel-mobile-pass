<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Logging Errors
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW7
 */
class LogController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::debug($request->all());
    }
}
