<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Google;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction;
use Spatie\LaravelMobilePass\Support\Config;

class HandleCallbackController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var class-string<HandleGoogleCallbackAction> $actionClass */
        $actionClass = Config::getActionClass('handle_google_callback', HandleGoogleCallbackAction::class);

        app($actionClass)->execute($request);

        return response()->noContent();
    }
}
