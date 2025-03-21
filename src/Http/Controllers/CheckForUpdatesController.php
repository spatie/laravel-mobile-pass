<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Http\Requests\CheckForUpdatesRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * Getting the Latest Version of a Pass
 * https://developer.apple.com/documentation/walletpasses/send-an-updated-pass
 */
class CheckForUpdatesController extends Controller
{
    public function __invoke(CheckForUpdatesRequest $request)
    {
        $pass = $request->mobilePass();

        if ($pass->wasUpdatedAfter($request->lastModifiedSinceHeaderValue())) {
            return $this->respondWithNewlyGeneratedPass($pass);
        }

        return response()->setNotModified();
    }

    protected function respondWithNewlyGeneratedPass(MobilePass $pass)
    {
        return response($pass->generate())
            ->header('Content-Type', 'application/vnd.apple.pkpass')
            ->setLastModified($pass->updated_at);
    }
}
