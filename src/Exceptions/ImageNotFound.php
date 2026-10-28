<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;

class ImageNotFound extends Exception implements MobilePassException
{
    public static function atPath(string $path): self
    {
        return new self("No image file found at path `{$path}`.");
    }

    public static function unsupportedUrl(string $url): self
    {
        return new self("Remote images must use an http or https URL, `{$url}` given.");
    }

    public static function couldNotDownload(string $url, ?int $status = null): self
    {
        $reason = $status === null ? '' : " (status {$status})";

        return new self("Could not download the remote image at `{$url}`{$reason}.");
    }
}
