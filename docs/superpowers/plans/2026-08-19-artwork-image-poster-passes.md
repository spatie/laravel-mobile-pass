# Artwork Image Support for Poster-Style Passes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `setArtworkImage()` / `setRemoteArtworkImage()` / `setLocaleArtworkImage()` / `setRemoteLocaleArtworkImage()` to `EventTicketPassBuilder` and `GenericPassBuilder`, so callers can attach the `artwork` image Apple's poster-style event ticket and poster-style generic pass layouts render.

**Architecture:** A new trait `HasArtworkImage` (in a new `src/Builders/Apple/Concerns/` directory) holds the four methods, storing images under the `artwork` key in the existing `$this->images` / `$this->locales[$lang]['images']` arrays exactly like every other image type. `EventTicketPassBuilder` and `GenericPassBuilder` each `use` the trait. No changes to the generic bundling code in `ApplePassBuilder` are needed — it already bundles any key present in those arrays.

**Tech Stack:** PHP 8.x, Laravel package (`spatie/laravel-mobile-pass`), Pest for tests.

## Global Constraints

- Only `EventTicketPassBuilder` and `GenericPassBuilder` get the artwork methods — not the shared `ApplePassBuilder` base class, and not other pass-type builders (Apple's poster layouts only apply to `eventTicket` and `generic` pass types).
- Method names: `setArtworkImage`, `setRemoteArtworkImage`, `setLocaleArtworkImage`, `setRemoteLocaleArtworkImage` — matching Apple's `artwork` asset name, not `strip` (which is a distinct, already-supported image).
- No handling of `preferredStyleSchemes` / activating the poster layout itself — out of scope.
- No new validation beyond the existing `Image` entity's file-existence check.

Spec: `docs/superpowers/specs/2026-08-19-artwork-image-poster-passes-design.md`

---

### Task 1: `HasArtworkImage` trait + wire into `EventTicketPassBuilder`

**Files:**
- Create: `src/Builders/Apple/Concerns/HasArtworkImage.php`
- Modify: `src/Builders/Apple/EventTicketPassBuilder.php`
- Test: `tests/Builders/Apple/EventTicketPassBuilderTest.php`

**Interfaces:**
- Consumes: `Spatie\LaravelMobilePass\Builders\Apple\Entities\Image` (constructor `new Image(string $x1Path, ?string $x2Path = null, ?string $x3Path = null)`, static `Image::makeRemote(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): Image`) — both already exist in `src/Builders/Apple/Entities/Image.php`. Protected properties `$this->images` (array) and `$this->locales` (array) from `ApplePassBuilder` (`src/Builders/Apple/ApplePassBuilder.php:110-114`).
- Produces: `HasArtworkImage` trait with public methods `setArtworkImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self`, `setRemoteArtworkImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self`, `setLocaleArtworkImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self`, `setRemoteLocaleArtworkImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self`. Later tasks (`GenericPassBuilder`) consume this trait by `use`-ing it.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/EventTicketPassBuilderTest.php`, after the existing `'registers a remote background image'` test (before the closing `'has a name'` test):

```php
it('bundles an artwork image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setArtworkImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('artwork.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@2x.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@3x.png'))->toBeTrue();
});

it('registers a remote artwork image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteArtworkImage('https://example.com/pass/artwork.png')
        ->save();

    expect($pass->images['artwork'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/artwork.png',
            'isRemote' => true,
        ]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::setArtworkImage()` (and `setRemoteArtworkImage()`).

- [ ] **Step 3: Create the `HasArtworkImage` trait**

Create `src/Builders/Apple/Concerns/HasArtworkImage.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Concerns;

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;

trait HasArtworkImage
{
    public function setArtworkImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->images['artwork'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteArtworkImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->images['artwork'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setLocaleArtworkImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->locales[$language]['images']['artwork'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleArtworkImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->locales[$language]['images']['artwork'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }
}
```

- [ ] **Step 4: Wire the trait into `EventTicketPassBuilder`**

Modify `src/Builders/Apple/EventTicketPassBuilder.php` — add the `use` import and the trait `use` statement:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::EventTicket;
```

The rest of the file (`validator()`, `compileData()`, closing brace) is unchanged.

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: PASS (all tests in the file, including the two new ones).

- [ ] **Step 6: Commit**

```bash
git add src/Builders/Apple/Concerns/HasArtworkImage.php src/Builders/Apple/EventTicketPassBuilder.php tests/Builders/Apple/EventTicketPassBuilderTest.php
git commit -m "Add artwork image support to EventTicketPassBuilder"
```

---

### Task 2: Wire `HasArtworkImage` into `GenericPassBuilder`

**Files:**
- Modify: `src/Builders/Apple/GenericPassBuilder.php`
- Test: `tests/Builders/Apple/GenericPassBuilderTest.php`

**Interfaces:**
- Consumes: `HasArtworkImage` trait from Task 1 (`src/Builders/Apple/Concerns/HasArtworkImage.php`), same method signatures as listed in Task 1.
- Produces: nothing new consumed by later tasks.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/GenericPassBuilderTest.php`, after the existing `'compiles back fields into the generic payload'` test (before the closing `'has a name'` test). This file doesn't import `PkPassReader` yet, so add that `use` statement too:

```php
<?php

use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

it('compiles back fields into the generic payload', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addBackField('terms', 'Terms and conditions apply.')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->toHaveKey('generic');
    expect($compiledData['generic'])->toHaveKey('backFields');
    expect($compiledData['generic']['backFields'])->toHaveCount(1);
    expect($compiledData['generic']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});

it('bundles an artwork image into the generated pass', function () {
    $generatedPass = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setArtworkImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('artwork.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@2x.png'))->toBeTrue()
        ->and($reader->containsFile('artwork@3x.png'))->toBeTrue();
});

it('registers a remote artwork image', function () {
    $pass = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteArtworkImage('https://example.com/pass/artwork.png')
        ->save();

    expect($pass->images['artwork'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/artwork.png',
            'isRemote' => true,
        ]);
});

it('has a name', function () {
    expect(GenericPassBuilder::name())->toBe('generic');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/GenericPassBuilderTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder::setArtworkImage()` (and `setRemoteArtworkImage()`).

- [ ] **Step 3: Wire the trait into `GenericPassBuilder`**

Modify `src/Builders/Apple/GenericPassBuilder.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\GenericApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class GenericPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::Generic;
```

The rest of the file (`validator()`, `compileData()`, closing brace) is unchanged.

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/GenericPassBuilderTest.php`
Expected: PASS (all tests in the file, including the two new ones).

- [ ] **Step 5: Commit**

```bash
git add src/Builders/Apple/GenericPassBuilder.php tests/Builders/Apple/GenericPassBuilderTest.php
git commit -m "Add artwork image support to GenericPassBuilder"
```

---

### Task 3: Locale artwork image bundling coverage

**Files:**
- Test: `tests/Builders/Apple/LocalizationTest.php`

**Interfaces:**
- Consumes: `EventTicketPassBuilder::setIconImage()` (existing), `EventTicketPassBuilder::setLocaleArtworkImage()` (from Task 1's trait), `PkPassReader::fromString()` / `containsFile()` (existing, already imported in this file).
- Produces: nothing consumed by later tasks.

This task adds coverage confirming that per-locale artwork images are bundled correctly. No production code changes are expected: `ApplePassBuilder::addLocaleDataToPass()` (`src/Builders/Apple/ApplePassBuilder.php:567-589`) already iterates every key in `$this->locales[$language]['images']` generically, so `artwork` is handled automatically once Task 1 adds `setLocaleArtworkImage()`. This test is a regression guard, not a driver for new code.

- [ ] **Step 1: Write the test**

Add to `tests/Builders/Apple/LocalizationTest.php`, directly after the existing `'bundles localized footer images into boarding passes'` test:

```php
it('bundles localized artwork images into event tickets', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage($imagePath)
        ->setLocaleArtworkImage('en', $imagePath, $imagePath)
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/artwork.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/artwork@2x.png'))->toBeTrue();
});
```

- [ ] **Step 2: Run the test**

Run: `vendor/bin/pest tests/Builders/Apple/LocalizationTest.php`
Expected: PASS immediately — `setLocaleArtworkImage()` already exists from Task 1, and the bundling code that writes `{lang}.lproj/{key}.png` is already generic over the `images` array keys, so no production code change is needed here. This confirms the "no changes needed to bundling code" claim from the design spec.

- [ ] **Step 3: Commit**

```bash
git add tests/Builders/Apple/LocalizationTest.php
git commit -m "Add locale artwork image bundling test coverage"
```

---

### Task 4: Document `setArtworkImage()` in the images guide

**Files:**
- Modify: `docs/basic-usage/adding-images.md`

**Interfaces:**
- Consumes: nothing (documentation only).
- Produces: nothing consumed by later tasks.

- [ ] **Step 1: Update the intro sentence and add a dedicated artwork paragraph**

In `docs/basic-usage/adding-images.md`, replace the paragraph at line 20 (`Alongside the logo and icon, you can set a strip, thumbnail, or background image...`) with:

```markdown
Alongside the logo and icon, you can set a strip, thumbnail, or background image with `setStripImage()`, `setThumbnailImage()`, and `setBackgroundImage()`. The background image is rendered behind event tickets:

```php
$builder->setBackgroundImage(public_path('images/background.png'));
```

Event tickets and generic passes also support an artwork image, which Apple renders as a full poster behind the pass content in its newer poster-style layouts:

```php
$builder->setArtworkImage(public_path('images/artwork.png'));
```
```

- [ ] **Step 2: Add a row to the "Recommended dimensions" table**

In the same file, in the table starting at line 42, add a row after the `Background` row (line 48):

```markdown
| Artwork | up to 1074 × 1344 | Event tickets and generic passes only. Large portrait image for Apple's poster-style layouts. |
```

So the table reads:

```markdown
| Image | 1x size (points) | Notes |
|---|---|---|
| Icon | 29 × 29 | Used in notifications and email attachments. Ship this one at minimum. |
| Logo | up to 160 × 50 | Top-left of the pass. |
| Thumbnail | up to 90 × 90 | Square artwork next to primary fields on event tickets and generic passes. |
| Strip | 375 × 123 (coupon) / 375 × 98 (event ticket) | Full-width image behind the primary fields. |
| Background | 180 × 220 | Event tickets only. Blurred and stretched by Wallet. |
| Artwork | up to 1074 × 1344 | Event tickets and generic passes only. Large portrait image for Apple's poster-style layouts. |
| Footer | 286 × 15 | Boarding passes only. Sits above the barcode. |
```

- [ ] **Step 3: Commit**

```bash
git add docs/basic-usage/adding-images.md
git commit -m "Document setArtworkImage in the adding-images guide"
```
