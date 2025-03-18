<?php

use Spatie\LaravelMobilePass\Support\PkPassReader;

beforeEach(function () {
    $this->passkeyFile = getTestSupportPath('passes/PkPassReader/valid.pkpass');

    $this->reader = PkPassReader::loadFromFile($this->passkeyFile);
});

it('can read a pass key from a file', function () {
    expect($this->reader->containsFile('pass.json'))->toBeTrue();
});

it('can read a pass key from a string', function () {
    $passkeyContent = file_get_contents($this->passkeyFile);

    $reader = PkPassReader::loadFromString($passkeyContent);

    expect($reader->containsFile('pass.json'))->toBeTrue();
});

it('can get all containing filenames', function () {
    expect($this->reader->containingFiles())->toBe([
        'signature',
        'manifest.json',
        'pass.json',
        'icon.png',
    ]);
});

it('can determine if the pass contains a given file', function () {
    expect($this->reader->containsFile('signature'))->toBeTrue();
    expect($this->reader->containsFile('non-existing-file'))->toBeFalse();
});

it('can get the manifest properties', function () {
    expect($this->reader->manifestProperties())->toBe([
        'pass.json' => '2b57d320d166cc8dbc18044414e9d2924d80f02a',
        'icon.png' => 'ee612bad12627ebc2e218ad7175c135692e0ca0e',
    ]);
});

it('can get a manifest property using dot notation', function () {
    expect($this->reader->manifestProperty('pass.json'))->toBe('2b57d320d166cc8dbc18044414e9d2924d80f02a');
    expect($this->reader->manifestProperty('non-existing'))->toBeNull();
});

it('can get the pass properties', function () {
    $passProperties = $this->reader->passProperties();

    expect($passProperties)->toHaveCount(8);
    expect($passProperties['description'])->toBe('Hello!');
});

it('can get a pass property using dot notation', function () {
    expect($this->reader->passProperty('description'))->toBe('Hello!');
    expect($this->reader->passProperty('semantics.seats.0.number'))->toBe('66F');
    expect($this->reader->passProperty('non-existing'))->toBeNull();
});
