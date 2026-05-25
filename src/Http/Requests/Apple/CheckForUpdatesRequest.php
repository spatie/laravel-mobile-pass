<?php

namespace Spatie\LaravelMobilePass\Http\Requests\Apple;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class CheckForUpdatesRequest extends FormRequest
{
    public function mobilePass(): MobilePass
    {
        $mobilePassClass = Config::mobilePassModel();

        return $mobilePassClass::query()
            ->where('pass_serial', $this->route('passSerial'))
            ->firstOrFail();
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
