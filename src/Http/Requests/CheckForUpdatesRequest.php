<?php

namespace Spatie\LaravelMobilePass\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CheckForUpdatesRequest extends FormRequest
{
    public function mobilePass(): MobilePass
    {
        return MobilePass::findOrFail($this->route('passSerial'));
    }

    public function lastModifiedSinceHeaderValue(): ?Carbon
    {
        $lastModifiedSince = $this->header('If-Modified-Since');

        if (! $lastModifiedSince) {
            return null;
        }

        return new Carbon($lastModifiedSince);
    }
}
