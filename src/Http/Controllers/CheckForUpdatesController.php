<?php

namespace Spatie\LaravelMobilePass\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * Getting the Latest Version of a Pass
 * https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988-CH0-SW6
 */
class CheckForUpdatesController extends Controller
{
    public function __invoke(Request $request)
    {
        $pass = MobilePass::findOrFail($request->passSerial);
        $lastModifiedSince = $request->header('If-Modified-Since');

        if ($lastModifiedSince) {
            $since = new Carbon($lastModifiedSince);

            if ($pass->updated_at <= $since) {
                return response(null, 304);
            }
        }

        return response(
            $pass->generate(),
            200,
            [
                'Content-Type' => 'application/vnd.apple.pkpass',
                'Last-Modified' => $pass->updated_at,
            ]
        );
    }
}
