<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelMobilePass\Events\ReceivedMobilePassLogEntriesEvent;

/**
 * Logging Errors
 * https://developer.apple.com/documentation/walletpasses/log-a-message
 */
class MobilePassLogController extends Controller
{
    public function __invoke(Request $request)
    {
        event(new ReceivedMobilePassLogEntriesEvent($request->json('logs')));
    }
}
