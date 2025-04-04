<?php

namespace Spatie\LaravelMobilePass\Support;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;

class DownloadableMobilePass implements Responsable
{
    public function __construct(
        protected string $passContent,
        protected string $downloadName = 'pass'
    ) {
        $this->downloadName = Str::beforeLast($this->downloadName, '.pkpass');
    }

    protected function headers()
    {
        return [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => "attachment; filename=\"{$this->downloadName}.pkpass\"",
        ];
    }

    public function toResponse($request)
    {
        return response($this->passContent)->withHeaders($this->headers());
    }
}
