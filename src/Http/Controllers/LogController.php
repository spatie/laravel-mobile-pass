<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Logging Errors
 * https://developer.apple.com/documentation/walletpasses/log-a-message
 */
class LogController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::debug($request->all());
    }
}
