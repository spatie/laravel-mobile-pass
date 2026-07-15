<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('sends link, text and image modules on the object to Google', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    $pass = GenericPassBuilder::make()
        ->setClass('membership')
        ->setObjectSuffix('alpha')
        ->setCardTitle('Acme Inc')
        ->addLink('https://example.com/my-card/alpha', 'My card')
        ->addLink('tel:+3232001212')
        ->addTextModule('Notes', 'Show this pass at the entrance', 'notes')
        ->addImageModule('https://cdn.example.com/member.png', 'photo')
        ->save();

    expect($pass->content['googleObjectPayload']['linksModuleData']['uris'][0]['uri'])
        ->toBe('https://example.com/my-card/alpha');

    Http::assertSent(function ($request) {
        expect($request['linksModuleData']['uris'][0]['uri'])->toBe('https://example.com/my-card/alpha');
        expect($request['linksModuleData']['uris'][0]['description'])->toBe('My card');
        expect($request['linksModuleData']['uris'][1]['uri'])->toBe('tel:+3232001212');
        expect($request['linksModuleData']['uris'][1])->not->toHaveKey('description');

        expect($request['textModulesData'][0]['header'])->toBe('Notes');
        expect($request['textModulesData'][0]['body'])->toBe('Show this pass at the entrance');
        expect($request['textModulesData'][0]['id'])->toBe('notes');

        expect($request['imageModulesData'][0]['mainImage']['sourceUri']['uri'])->toBe('https://cdn.example.com/member.png');
        expect($request['imageModulesData'][0]['id'])->toBe('photo');

        return true;
    });
});

it('makes the module methods available on every object builder type', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setAccountId('AC-42')
        ->setAccountName('Jane Doe')
        ->addLink('https://example.com/points/jane', 'My points')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['linksModuleData']['uris'][0]['uri'])->toBe('https://example.com/points/jane');
        expect($request['linksModuleData']['uris'][0]['description'])->toBe('My points');

        return true;
    });
});

it('omits the module keys entirely when none are set', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    GenericPassBuilder::make()
        ->setClass('membership')
        ->setObjectSuffix('beta')
        ->setCardTitle('Acme Inc')
        ->save();

    Http::assertSent(function ($request) {
        expect($request->data())->not->toHaveKey('linksModuleData');
        expect($request->data())->not->toHaveKey('textModulesData');
        expect($request->data())->not->toHaveKey('imageModulesData');

        return true;
    });
});
