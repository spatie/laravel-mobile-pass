<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Events\AppleMobilePassLogsReceived;

/**
 * Logging Errors
 * https://developer.apple.com/documentation/walletpasses/log-a-message
 */
class MobilePassLogController extends Controller
{
    public function __invoke(Request $request): void
    {
        event(new AppleMobilePassLogsReceived($request->json('logs')));
    }
}
