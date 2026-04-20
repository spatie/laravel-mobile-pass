<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Apple\DownloadableMobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class DownloadApplePassController extends Controller
{
    public function __invoke(Request $request, string $mobilePass): DownloadableMobilePass
    {
        abort_unless($request->hasValidSignature(), 403);

        $modelClass = Config::mobilePassModel();

        /** @var MobilePass $pass */
        $pass = $modelClass::query()->findOrFail($mobilePass);

        return $pass->download();
    }
}
