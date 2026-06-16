<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('sends location, link, text and image modules to Google', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('membership')
        ->setIssuerName('Spatie')
        ->addLocation(37.424015, -122.09259)
        ->addLink('https://spatie.be', 'Visit our site')
        ->addLink('tel:+3232001212')
        ->addTextModule('Opening hours', 'Mon to Fri, 9 to 5', 'hours')
        ->addImageModule('https://cdn.example.com/promo.png', 'promo')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['locations'][0]['latitude'])->toBe(37.424015);
        expect($request['locations'][0]['longitude'])->toBe(-122.09259);

        expect($request['linksModuleData']['uris'][0]['uri'])->toBe('https://spatie.be');
        expect($request['linksModuleData']['uris'][0]['description'])->toBe('Visit our site');
        expect($request['linksModuleData']['uris'][1]['uri'])->toBe('tel:+3232001212');
        expect($request['linksModuleData']['uris'][1])->not->toHaveKey('description');

        expect($request['textModulesData'][0]['header'])->toBe('Opening hours');
        expect($request['textModulesData'][0]['body'])->toBe('Mon to Fri, 9 to 5');
        expect($request['textModulesData'][0]['id'])->toBe('hours');

        expect($request['imageModulesData'][0]['mainImage']['sourceUri']['uri'])->toBe('https://cdn.example.com/promo.png');
        expect($request['imageModulesData'][0]['id'])->toBe('promo');

        return true;
    });
});

it('omits the module keys entirely when none are set', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('membership')->setIssuerName('Spatie')->save();

    Http::assertSent(function ($request) {
        expect($request->data())->not->toHaveKey('locations');
        expect($request->data())->not->toHaveKey('linksModuleData');
        expect($request->data())->not->toHaveKey('textModulesData');
        expect($request->data())->not->toHaveKey('imageModulesData');

        return true;
    });
});

it('exposes the same module builders on every class type', function () {
    Http::fake(['*/loyaltyClass' => Http::response([], 200)]);

    LoyaltyPassClass::make('coffee')
        ->setIssuerName('Spatie')
        ->setProgramName('Coffee Club')
        ->addTextModule('Perks', 'Every 10th coffee is free')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['textModulesData'][0]['header'])->toBe('Perks');
        expect($request['textModulesData'][0]['body'])->toBe('Every 10th coffee is free');
        expect($request['textModulesData'][0])->not->toHaveKey('id');

        return true;
    });
});

it('hydrates module data back from Google', function () {
    Http::fake(['*/genericClass/3388.membership' => Http::response([
        'id' => '3388.membership',
        'locations' => [['latitude' => 37.424015, 'longitude' => -122.09259]],
        'linksModuleData' => ['uris' => [['uri' => 'https://spatie.be', 'description' => 'Visit our site']]],
        'textModulesData' => [['header' => 'Opening hours', 'body' => 'Mon to Fri, 9 to 5', 'id' => 'hours']],
        'imageModulesData' => [['mainImage' => ['sourceUri' => ['uri' => 'https://cdn.example.com/promo.png']], 'id' => 'promo']],
    ], 200)]);

    $class = GenericPassClass::find('membership');

    expect($class->getLocations()[0]->latitude)->toBe(37.424015);
    expect($class->getLocations()[0]->longitude)->toBe(-122.09259);
    expect($class->getLinks()[0]->uri)->toBe('https://spatie.be');
    expect($class->getLinks()[0]->description)->toBe('Visit our site');
    expect($class->getTextModules()[0]->header)->toBe('Opening hours');
    expect($class->getTextModules()[0]->body)->toBe('Mon to Fri, 9 to 5');
    expect($class->getTextModules()[0]->id)->toBe('hours');
    expect($class->getImageModules()[0]->image->publicUrl())->toBe('https://cdn.example.com/promo.png');
    expect($class->getImageModules()[0]->id)->toBe('promo');
});
