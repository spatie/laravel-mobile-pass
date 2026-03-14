<?php

// Stubs for PHPStan analysis — these functions are defined in tests/Pest.php
// and are available at runtime, but PHPStan needs them declared here.

if (! function_exists('getTestSupportPath')) {
    function getTestSupportPath(string $path): string
    {
        return __DIR__.'/tests/TestSupport/'.$path;
    }
}
