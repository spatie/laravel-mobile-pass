<?php

namespace Spatie\LaravelMobilePass\Support\Apple;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DownloadableMobilePass implements Responsable
{
    public function __construct(
        protected string $passContent,
        protected string $downloadName = 'pass'
    ) {
        $this->downloadName = Str::beforeLast($this->downloadName, '.pkpass');
    }

    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => "inline; filename=\"{$this->downloadName}.pkpass\"",
        ];
    }

    public function toResponse($request): Response
    {
        return response($this->passContent)->withHeaders($this->headers());
    }
}
