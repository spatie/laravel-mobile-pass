<?php

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Mailables\TestMail;

it('can return a downloadable pass', function (?string $customName) {
    Route::get('test', function () use ($customName) {
        $mobilePass = MobilePass::factory()->create();

        return $customName
            ? $mobilePass->download($customName)
            : $mobilePass->download();
    });

    $expectedName = $customName ?? 'pass';

    $this
        ->get('test')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/vnd.apple.pkpass')
        ->assertDownload("{$expectedName}.pkpass");
})->with([
    null,
    'customName',
]);

it('implements responsible and uses download_name as the download name', function (?string $customName) {
    Route::get('test', function () use ($customName) {
        $mobilePass = MobilePass::factory(['download_name' => $customName])->create();

        return $mobilePass;
    });

    $expectedName = $customName ?? 'pass';

    $this
        ->get('test')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/vnd.apple.pkpass')
        ->assertDownload("{$expectedName}.pkpass");
})->with([
    null,
    'customName',
]);

it('can be used as an attachment', function (?string $customName) {
    $mobilePass = MobilePass::factory(['download_name' => $customName])->create();

    $mailable = new TestMail($mobilePass);

    $expectedName = $customName ?? 'pass';

    $mailable->assertHasAttachedFileName(
        name: "{$expectedName}.pkpass",
        options: [
            'mime' => 'application/vnd.apple.pkpass',
        ]
    );
})->with([
    null,
    'customName',
]);
