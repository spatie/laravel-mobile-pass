# Poster Layout Activation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `EventTicketPassBuilder::usePosterLayout()` (sets `preferredStyleSchemes`) and `GenericPassBuilder::addPosterHeaderField()`/`addPosterPrimaryField()`/`addPosterFooterField()`/`addPosterBackField()` (populate a new `posterGeneric` top-level block), so callers can actually activate the poster layouts that the previously-added `artwork` image supports.

**Architecture:** `EventTicketPassBuilder` gets a new scalar property + setter that serializes into a top-level `preferredStyleSchemes` array. `GenericPassBuilder` gets four new field collections + setters, serialized into a new top-level `posterGeneric` object, built via a `makeFieldContent()` helper extracted from `ApplePassBuilder::addField()` so the logic isn't duplicated. Both validators get new rules so the keys survive Laravel's `validated()` filtering. Both builders get `uncompileContent()` overrides so the new state round-trips through `save()`/`hydrate()`.

**Tech Stack:** PHP 8.x, Laravel package (`spatie/laravel-mobile-pass`), Pest for tests.

## Global Constraints

- `EventTicketPassBuilder::usePosterLayout(): self` takes no arguments and sets `preferredStyleSchemes = ['posterEventTicket', 'eventTicket']` — no raw `setPreferredStyleSchemes(array $schemes)` escape hatch.
- `GenericPassBuilder` poster fields go through `addPosterHeaderField()`, `addPosterPrimaryField()`, `addPosterFooterField()`, `addPosterBackField()` — same signature shape as the existing `addHeaderField()` family. Adding any one of these is what makes `posterGeneric` appear in `pass.json`; there is no separate enable/toggle method for Generic.
- No validation or exception requiring an artwork image before poster mode is enabled — dropped from scope. A poster-enabled pass with no artwork is still valid; Wallet just falls back to the classic layout. This is documentation guidance only.
- The `makeFieldContent()` extraction must be a pure refactor — `ApplePassBuilder::addField()`'s existing behavior and public signature are unchanged, and every existing test that exercises `addField()`/`addHeaderField()`/`addSecondaryField()`/`addAuxiliaryField()`/`addBackField()` (across every pass type) must keep passing unmodified.
- New validator rules: `'preferredStyleSchemes' => ['nullable', 'array']` on `EventTicketApplePassValidator`; `'posterGeneric.headerFields'`, `'posterGeneric.primaryFields'`, `'posterGeneric.footerFields'`, `'posterGeneric.backFields'` (all `['nullable', 'array']`) on `GenericApplePassValidator`.

Spec: `docs/superpowers/specs/2026-08-19-poster-layout-activation-design.md`

---

### Task 1: Extract `makeFieldContent()` in `ApplePassBuilder`

**Files:**
- Modify: `src/Builders/Apple/ApplePassBuilder.php:253-289` (the `addField()` method)

**Interfaces:**
- Consumes: nothing new.
- Produces: `protected function makeFieldContent(string $key, string $value, ?string $label = null, ?string $changeMessage = null, ?DateType $dateStyle = null, ?TimeStyleType $timeStyle = null, ?bool $showDateAsRelative = null): FieldContent`. Task 3's new `GenericPassBuilder` methods call this directly (it's `protected`, and `GenericPassBuilder extends ApplePassBuilder`).

This is a pure extract-method refactor: no new test is written because no new behavior is introduced. Verification is that the full existing suite still passes identically before and after.

- [ ] **Step 1: Confirm the current `addField()` implementation matches what this task will change**

Read `src/Builders/Apple/ApplePassBuilder.php:253-289`. It currently reads:

```php
public function addField(
    string $key,
    string $value,
    FieldType $type = FieldType::Primary,
    ?string $label = null,
    ?string $changeMessage = null,
    ?DateType $dateStyle = null,
    ?TimeStyleType $timeStyle = null,
    ?bool $showDateAsRelative = null,
): self {
    $field = FieldContent::make($key)
        ->withValue($value)
        ->withLabel($label ?? Str::headline($key));

    if ($changeMessage !== null) {
        $field->showMessageWhenChanged($changeMessage);
    }

    if ($dateStyle !== null) {
        $field->usingDateType($dateStyle);
    }

    if ($timeStyle !== null) {
        $field->usingTimeType($timeStyle);
    }

    if ($showDateAsRelative) {
        $field->showDateAsRelative();
    }

    $property = $type->value;

    $this->{$property} ??= collect();
    $this->{$property}[$key] = $field;

    return $this;
}
```

If it doesn't match (e.g. line numbers shifted), locate the method by name instead — the body content is what matters.

- [ ] **Step 2: Replace it with the extracted helper + a slimmer `addField()`**

Replace the whole method from Step 1 with:

```php
protected function makeFieldContent(
    string $key,
    string $value,
    ?string $label = null,
    ?string $changeMessage = null,
    ?DateType $dateStyle = null,
    ?TimeStyleType $timeStyle = null,
    ?bool $showDateAsRelative = null,
): FieldContent {
    $field = FieldContent::make($key)
        ->withValue($value)
        ->withLabel($label ?? Str::headline($key));

    if ($changeMessage !== null) {
        $field->showMessageWhenChanged($changeMessage);
    }

    if ($dateStyle !== null) {
        $field->usingDateType($dateStyle);
    }

    if ($timeStyle !== null) {
        $field->usingTimeType($timeStyle);
    }

    if ($showDateAsRelative) {
        $field->showDateAsRelative();
    }

    return $field;
}

public function addField(
    string $key,
    string $value,
    FieldType $type = FieldType::Primary,
    ?string $label = null,
    ?string $changeMessage = null,
    ?DateType $dateStyle = null,
    ?TimeStyleType $timeStyle = null,
    ?bool $showDateAsRelative = null,
): self {
    $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

    $property = $type->value;

    $this->{$property} ??= collect();
    $this->{$property}[$key] = $field;

    return $this;
}
```

Double-check no other method in the file was between the old `addField()` and `updateField()` that you might have accidentally overwritten — `updateField()` should immediately follow `addField()`, unchanged.

- [ ] **Step 3: Run the full test suite to confirm no behavior changed**

Run: `vendor/bin/pest`
Expected: PASS — same pass/fail counts as before this change (this is a pure refactor; if anything that previously passed now fails, the extraction introduced a behavior difference and must be fixed before proceeding).

- [ ] **Step 4: Commit**

```bash
git add src/Builders/Apple/ApplePassBuilder.php
git commit -m "Extract makeFieldContent helper from addField"
```

---

### Task 2: `usePosterLayout()` on `EventTicketPassBuilder`

**Files:**
- Modify: `src/Builders/Apple/EventTicketPassBuilder.php`
- Modify: `src/Builders/Apple/Validators/EventTicketApplePassValidator.php`
- Test: `tests/Builders/Apple/EventTicketPassBuilderTest.php`

**Interfaces:**
- Consumes: nothing from Task 1 (this task is independent of the `makeFieldContent()` extraction).
- Produces: `EventTicketPassBuilder::usePosterLayout(): self`. Not consumed by any other task.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/EventTicketPassBuilderTest.php`, after the existing `'registers a remote artwork image'` test (before the closing `'has a name'` test):

```php
it('activates the poster layout via preferredStyleSchemes', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->usePosterLayout()
        ->data();

    expect($compiledData['preferredStyleSchemes'])->toBe(['posterEventTicket', 'eventTicket']);
});

it('omits preferredStyleSchemes when the poster layout is not used', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('preferredStyleSchemes');
});

it('round-trips preferredStyleSchemes through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->usePosterLayout()
        ->save();

    expect($model->builder()->data()['preferredStyleSchemes'])
        ->toBe(['posterEventTicket', 'eventTicket']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::usePosterLayout()`.

- [ ] **Step 3: Add the property, method, `compileData()` change, and `uncompileContent()` override**

Replace the full contents of `src/Builders/Apple/EventTicketPassBuilder.php` with:

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

    protected ?array $preferredStyleSchemes = null;

    protected static function validator(): ApplePassValidator
    {
        return new EventTicketApplePassValidator;
    }

    public function usePosterLayout(): self
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

        return $this;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'eventTicket' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                    'backFields' => $this->backFields?->values()->toArray(),
                ]),
                'preferredStyleSchemes' => $this->preferredStyleSchemes,
            ],
        );
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $this->preferredStyleSchemes = $this->data['preferredStyleSchemes'] ?? null;
    }
}
```

- [ ] **Step 4: Add the validator rule**

Modify `src/Builders/Apple/Validators/EventTicketApplePassValidator.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

class EventTicketApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'eventTicket.headerFields' => ['nullable', 'array'],
            'eventTicket.primaryFields' => ['nullable', 'array'],
            'eventTicket.secondaryFields' => ['nullable', 'array'],
            'eventTicket.auxiliaryFields' => ['nullable', 'array'],
            'eventTicket.backFields' => ['nullable', 'array'],
            'preferredStyleSchemes' => ['nullable', 'array'],
        ]);
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: PASS (all tests in the file, including the three new ones).

- [ ] **Step 6: Commit**

```bash
git add src/Builders/Apple/EventTicketPassBuilder.php src/Builders/Apple/Validators/EventTicketApplePassValidator.php tests/Builders/Apple/EventTicketPassBuilderTest.php
git commit -m "Add usePosterLayout to EventTicketPassBuilder"
```

---

### Task 3: `addPoster*Field()` family on `GenericPassBuilder`

**Files:**
- Create: `src/Enums/PosterFieldType.php`
- Modify: `src/Builders/Apple/GenericPassBuilder.php`
- Modify: `src/Builders/Apple/Validators/GenericApplePassValidator.php`
- Test: `tests/Builders/Apple/GenericPassBuilderTest.php`

**Interfaces:**
- Consumes: `ApplePassBuilder::makeFieldContent(string $key, string $value, ?string $label = null, ?string $changeMessage = null, ?DateType $dateStyle = null, ?TimeStyleType $timeStyle = null, ?bool $showDateAsRelative = null): FieldContent` from Task 1.
- Produces: `GenericPassBuilder::addPosterHeaderField()`, `addPosterPrimaryField()`, `addPosterFooterField()`, `addPosterBackField()` — all `(string $key, string $value, ?string $label = null, ?string $changeMessage = null, ?DateType $dateStyle = null, ?TimeStyleType $timeStyle = null, ?bool $showDateAsRelative = null): self`. Not consumed by any other task.

This task mirrors the existing `addHeaderField()`/`addSecondaryField()`/`addAuxiliaryField()`/`addBackField()` pattern on `ApplePassBuilder`, which are all thin wrappers around a single `addField(..., FieldType $type)` dispatcher — rather than four independent methods with duplicated bodies, this task adds a `PosterFieldType` enum (mirroring the existing `FieldType` enum) and a single `addPosterField(..., PosterFieldType $type)` dispatcher, with the four public methods as thin wrappers around it.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/GenericPassBuilderTest.php`, after the existing `'registers a remote artwork image'` test (before the closing `'has a name'` test):

```php
it('bundles poster fields into the posterGeneric block', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addPosterHeaderField('event', 'Laracon EU')
        ->addPosterPrimaryField('venue', 'Amsterdam')
        ->addPosterFooterField('note', 'Doors open at 6pm')
        ->addPosterBackField('terms', 'Terms and conditions apply.')
        ->data();

    expect($compiledData)->toHaveKey('posterGeneric');
    expect($compiledData['posterGeneric'])->toHaveKeys([
        'headerFields',
        'primaryFields',
        'footerFields',
        'backFields',
    ]);
    expect($compiledData['posterGeneric']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
    expect($compiledData['posterGeneric']['footerFields'][0])->toMatchArray([
        'key' => 'note',
        'value' => 'Doors open at 6pm',
    ]);
});

it('omits posterGeneric when no poster fields are added', function () {
    $compiledData = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('posterGeneric');
});

it('round-trips poster fields through save and hydrate', function () {
    $model = GenericPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->addPosterHeaderField('event', 'Laracon EU')
        ->addPosterBackField('terms', 'Terms and conditions apply.')
        ->save();

    $hydratedData = $model->builder()->data();

    expect($hydratedData['posterGeneric']['headerFields'][0])->toMatchArray([
        'key' => 'event',
        'value' => 'Laracon EU',
    ]);
    expect($hydratedData['posterGeneric']['backFields'][0])->toMatchArray([
        'key' => 'terms',
        'value' => 'Terms and conditions apply.',
    ]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/GenericPassBuilderTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder::addPosterHeaderField()`.

- [ ] **Step 3: Create the `PosterFieldType` enum**

Create `src/Enums/PosterFieldType.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Enums;

enum PosterFieldType: string
{
    case Header = 'posterHeaderFields';
    case Primary = 'posterPrimaryFields';
    case Footer = 'posterFooterFields';
    case Back = 'posterBackFields';
}
```

The enum's value is the PHP property name on `GenericPassBuilder` (matching the existing `FieldType` enum's convention, where the value is also the property name it dispatches to).

- [ ] **Step 4: Add the properties, dispatcher, wrapper methods, `compileData()` change, and `uncompileContent()` override**

Replace the full contents of `src/Builders/Apple/GenericPassBuilder.php` with:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\GenericApplePassValidator;
use Spatie\LaravelMobilePass\Enums\DateType;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\PosterFieldType;
use Spatie\LaravelMobilePass\Enums\TimeStyleType;

class GenericPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::Generic;

    protected ?Collection $posterHeaderFields = null;

    protected ?Collection $posterPrimaryFields = null;

    protected ?Collection $posterFooterFields = null;

    protected ?Collection $posterBackFields = null;

    protected static function validator(): ApplePassValidator
    {
        return new GenericApplePassValidator;
    }

    public function addPosterHeaderField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Header, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterPrimaryField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Primary, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterFooterField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Footer, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addPosterBackField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addPosterField($key, $value, PosterFieldType::Back, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    protected function addPosterField(
        string $key,
        string $value,
        PosterFieldType $type,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

        $property = $type->value;

        $this->{$property} ??= collect();
        $this->{$property}[$key] = $field;

        return $this;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'generic' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                    'backFields' => $this->backFields?->values()->toArray(),
                ]),
                'posterGeneric' => array_filter([
                    'headerFields' => $this->posterHeaderFields?->values()->toArray(),
                    'primaryFields' => $this->posterPrimaryFields?->values()->toArray(),
                    'footerFields' => $this->posterFooterFields?->values()->toArray(),
                    'backFields' => $this->posterBackFields?->values()->toArray(),
                ]),
            ],
        );
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $posterGeneric = $this->data['posterGeneric'] ?? [];

        $propertyToJsonKey = [
            'posterHeaderFields' => 'headerFields',
            'posterPrimaryFields' => 'primaryFields',
            'posterFooterFields' => 'footerFields',
            'posterBackFields' => 'backFields',
        ];

        foreach ($propertyToJsonKey as $property => $jsonKey) {
            $this->{$property} = collect();

            foreach ($posterGeneric[$jsonKey] ?? [] as $field) {
                $this->{$property}[$field['key']] = FieldContent::fromArray($field);
            }
        }
    }
}
```

- [ ] **Step 5: Add the validator rules**

Modify `src/Builders/Apple/Validators/GenericApplePassValidator.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Validators;

class GenericApplePassValidator extends ApplePassValidator
{
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'generic.headerFields' => ['nullable', 'array'],
            'generic.primaryFields' => ['nullable', 'array'],
            'generic.secondaryFields' => ['nullable', 'array'],
            'generic.auxiliaryFields' => ['nullable', 'array'],
            'generic.backFields' => ['nullable', 'array'],
            'posterGeneric.headerFields' => ['nullable', 'array'],
            'posterGeneric.primaryFields' => ['nullable', 'array'],
            'posterGeneric.footerFields' => ['nullable', 'array'],
            'posterGeneric.backFields' => ['nullable', 'array'],
        ]);
    }
}
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/GenericPassBuilderTest.php`
Expected: PASS (all tests in the file, including the three new ones).

- [ ] **Step 7: Commit**

```bash
git add src/Enums/PosterFieldType.php src/Builders/Apple/GenericPassBuilder.php src/Builders/Apple/Validators/GenericApplePassValidator.php tests/Builders/Apple/GenericPassBuilderTest.php
git commit -m "Add addPoster*Field family and posterGeneric block to GenericPassBuilder"
```

---

### Task 4: Documentation

**Files:**
- Modify: `docs/available-pass-types/event-ticket.md`
- Modify: `docs/available-pass-types/generic.md`
- Modify: `docs/basic-usage/adding-images.md`

**Interfaces:**
- Consumes: nothing (documentation only).
- Produces: nothing consumed by later tasks.

- [ ] **Step 1: Mention `usePosterLayout()` in the event ticket doc**

The current file reads (in full):

```markdown
---
title: Event ticket
weight: 3
---

Event tickets cover concerts, festivals, sports events, conferences, and anything else where someone shows up at a specific time and place. Both platforms have a dedicated `EventTicketPassBuilder`.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setOrganizationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->addField('event', 'Beatles Live at Shea')
    ->addSecondaryField('section', 'B12')
    ->addSecondaryField('seat', 'Row 8, Seat 22')
    ->save();
```

## Google

...(Google example, unchanged)...
```

Read the actual current file first to confirm it still matches. Add a short paragraph directly after the Apple code sample (still inside the `## Apple` section, before `## Google`):

```markdown
Call `usePosterLayout()` to opt into Apple's poster-style layout on supported devices, which renders the `artwork` image (see [Adding images](../basic-usage/adding-images)) as a full-bleed background. Older devices fall back to the classic layout automatically:

```php
EventTicketPassBuilder::make()
    // ...
    ->setArtworkImage(public_path('images/artwork.png'))
    ->usePosterLayout()
    ->save();
```
```

- [ ] **Step 2: Mention the `addPoster*Field()` family in the generic doc**

In `docs/available-pass-types/generic.md`, read the current file first, then add a short paragraph after the existing Apple code sample (still inside `## Apple`, before `## Google`):

```markdown
Generic passes also support Apple's poster layout via a separate set of fields — `addPosterHeaderField()`, `addPosterPrimaryField()`, `addPosterFooterField()`, and `addPosterBackField()` — which populate a `posterGeneric` block shown alongside the classic layout. Set an `artwork` image (see [Adding images](../basic-usage/adding-images)) for it to render correctly:

```php
GenericPassBuilder::make()
    // ...
    ->setArtworkImage(public_path('images/artwork.png'))
    ->addPosterHeaderField('event', 'Spatie Conference 2026')
    ->addPosterPrimaryField('track', 'All-access')
    ->save();
```
```

- [ ] **Step 3: Replace the "not supported yet" caveat in the images guide**

In `docs/basic-usage/adding-images.md`, find this paragraph (added by a previous branch):

```markdown
Opting a pass into the poster layout (`preferredStyleSchemes`) isn't supported by this package yet, so the artwork image is bundled into the pass but won't be rendered as a poster until that's added — the file is there and ready for when it is.
```

Replace it with:

```markdown
Set the artwork image *before* opting a pass into the poster layout (`EventTicketPassBuilder::usePosterLayout()`, or any `GenericPassBuilder::addPoster*Field()` call) — Wallet silently falls back to the classic layout if no artwork is present, rather than raising an error.
```

- [ ] **Step 4: Commit**

```bash
git add docs/available-pass-types/event-ticket.md docs/available-pass-types/generic.md docs/basic-usage/adding-images.md
git commit -m "Document usePosterLayout and the addPoster*Field family"
```
