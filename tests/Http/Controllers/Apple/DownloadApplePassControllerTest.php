<?php

use Illuminate\Support\Facades\URL;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serves the pkpass at a signed url', function () {
    $pass = MobilePass::factory()->create();

    $url = URL::signedRoute('mobile-pass.apple.download', ['mobilePass' => $pass->id]);

    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.apple.pkpass');
});

it('rejects an unsigned url', function () {
    $pass = MobilePass::factory()->create();

    $this->get(route('mobile-pass.apple.download', ['mobilePass' => $pass->id]))
        ->assertForbidden();
});
