<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Events\ReceivedAppleMobilePassLogEntriesEvent;

/**
 * Logging Errors
 * https://developer.apple.com/documentation/walletpasses/log-a-message
 */
class MobilePassLogController extends Controller
{
    public function __invoke(Request $request)
    {
        event(new ReceivedAppleMobilePassLogEntriesEvent($request->json('logs')));
    }
}
