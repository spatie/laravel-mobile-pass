<?php

use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('can read arbitrary file content from a pkpass zip', function () {
    $reader = PkPassReader::fromFile(getTestSupportPath('passes/PkPassReader/valid.pkpass'));

    expect($reader->fileContent('pass.json'))->not->toBeNull();
    expect($reader->fileContent('does-not-exist.txt'))->toBeNull();
});

it('bundles locale strings into the generated pass', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'English Value'])
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/pass.strings'))->toBeTrue();
    expect($reader->fileContent('en.lproj/pass.strings'))->toContain('"LOC_KEY" = "English Value";');
});

it('merges locale strings on repeated calls for the same language', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY_1' => 'First'])
        ->addLocaleStrings('en', ['LOC_KEY_2' => 'Second'])
        ->generate();

    $reader = PkPassReader::fromString($pass);
    $content = $reader->fileContent('en.lproj/pass.strings');

    expect($content)
        ->toContain('"LOC_KEY_1" = "First";')
        ->toContain('"LOC_KEY_2" = "Second";');
});

it('produces separate pass.strings files for each language', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'English'])
        ->addLocaleStrings('it', ['LOC_KEY' => 'Italiano'])
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/pass.strings'))->toBeTrue();
    expect($reader->containsFile('it.lproj/pass.strings'))->toBeTrue();
    expect($reader->fileContent('en.lproj/pass.strings'))->toContain('"LOC_KEY" = "English";');
    expect($reader->fileContent('it.lproj/pass.strings'))->toContain('"LOC_KEY" = "Italiano";');
});

it('includes locale string files in the manifest', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'English'])
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->manifestProperties())->toHaveKey('en.lproj/pass.strings');
});

it('bundles localized images into the generated pass', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage($imagePath)
        ->setLocaleLogoImage('en', $imagePath, $imagePath, $imagePath)
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/logo.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/logo@2x.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/logo@3x.png'))->toBeTrue();
});

it('bundles multiple localized image types per language', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage($imagePath)
        ->setLocaleLogoImage('en', $imagePath)
        ->setLocaleStripImage('en', $imagePath)
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/logo.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/strip.png'))->toBeTrue();
});

it('persists locale strings through save and hydrate', function () {
    $builder = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'English']);

    $model = $builder->save();

    expect($model->locales)->toBe([
        'en' => ['strings' => ['LOC_KEY' => 'English']],
    ]);

    $reader = PkPassReader::fromString($model->builder()->generate());

    expect($reader->containsFile('en.lproj/pass.strings'))->toBeTrue();
    expect($reader->fileContent('en.lproj/pass.strings'))->toContain('"LOC_KEY" = "English";');
});

it('produces identical output before and after save/hydrate', function () {
    $builder = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'English'])
        ->addLocaleStrings('it', ['LOC_KEY' => 'Italiano']);

    $model = $builder->save();

    expect($model->generate())->toBe($builder->generate());
});

it('allows updating locale strings after save', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addLocaleStrings('en', ['LOC_KEY' => 'Original'])
        ->save();

    $model->builder()
        ->addLocaleStrings('en', ['LOC_KEY' => 'Updated'])
        ->save();

    $model->refresh();

    $reader = PkPassReader::fromString($model->generate());

    expect($reader->fileContent('en.lproj/pass.strings'))->toContain('"LOC_KEY" = "Updated";');
});

it('persists remote locale images on the saved model', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteLocaleLogoImage('en', 'https://example.com/logo-en.png')
        ->save();

    expect($model->locales['en']['images']['logo'])
        ->toMatchArray(['x1Path' => 'https://example.com/logo-en.png', 'isRemote' => true]);
});

it('stores null in the locales column when no locales are set', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    expect($model->locales)->toBeNull();
});

it('persists local locale images through save and hydrate', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage($imagePath)
        ->setLocaleLogoImage('en', $imagePath, $imagePath)
        ->save();

    $reader = PkPassReader::fromString($model->builder()->generate());

    expect($reader->containsFile('en.lproj/logo.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/logo@2x.png'))->toBeTrue();
});

it('bundles localized footer images into boarding passes', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $pass = AirlinePassBuilder::make()
        ->setOrganizationName('My Airline')
        ->setSerialNumber('123')
        ->setDescription('Test Boarding Pass')
        ->setIconImage($imagePath)
        ->setLocaleFooterImage('en', $imagePath, $imagePath)
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/footer.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/footer@2x.png'))->toBeTrue();
});

it('stores remote locale footer images on boarding pass model', function () {
    $model = AirlinePassBuilder::make()
        ->setOrganizationName('My Airline')
        ->setSerialNumber('123')
        ->setDescription('Test Boarding Pass')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteLocaleFooterImage('en', 'https://example.com/footer-en.png')
        ->save();

    expect($model->locales['en']['images']['footer'])
        ->toMatchArray(['x1Path' => 'https://example.com/footer-en.png', 'isRemote' => true]);
});
