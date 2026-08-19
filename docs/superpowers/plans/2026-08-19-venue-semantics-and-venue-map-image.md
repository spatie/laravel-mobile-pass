# Venue Semantics and Venue Map Image Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add all 16 of Apple's venue-related semantic tags as setters on `EventTicketPassBuilder`, plus `setVenueMapImage()` (and its remote/locale variants) to bundle `venueMap.png`, the image Apple's event guide panel shows in the pass's poster layout.

**Architecture:** `EventTicketPassBuilder` gets 16 new typed properties (13 `?string`/`?Carbon`, 1 `?Location`) with one setter each, mirroring `BoardingPassBuilder`'s existing per-semantic-field style, serialized via new `compileSemantics()`/`uncompileSemantics()` overrides. A `parseSemanticDate()` helper gets extracted from `BoardingPassBuilder` to the shared `ApplePassBuilder` base class so both builders can reuse it (mirrors the `makeFieldContent()` extraction earlier in this branch). `setVenueMapImage()` and its 3 variants are added directly to `EventTicketPassBuilder` (no trait — single-owner, like `setFooterImage()` on `BoardingPassBuilder`), reusing the existing generic image-bundling mechanism with no changes needed there.

**Tech Stack:** PHP 8.x, Laravel package (`spatie/laravel-mobile-pass`), Pest for tests.

## Global Constraints

- No validator changes — `ApplePassValidator::rules()` already declares a blanket `'semantics' => []` rule, and none of `BoardingPassBuilder`'s existing ~15 semantic fields have individual validator rules either. The new venue fields pass through the same way.
- No coupling/validation between venue semantics and `venueMap` — no exception or check requiring one before the other. Same no-enforcement decision as artwork/poster-layout earlier in this branch.
- `venueMap` image methods and venue semantic setters live only on `EventTicketPassBuilder` — not `GenericPassBuilder`, not the shared `ApplePassBuilder` base class.
- The `parseSemanticDate()` extraction must be a pure refactor — `BoardingPassBuilder`'s existing behavior is unchanged, and every existing test exercising its semantic date fields must keep passing unmodified.
- Image key for bundling must be exactly `venueMap` (camelCase) so it serializes as `venueMap.png`/`@2x`/`@3x`, matching Apple's required filename.

Spec: `docs/superpowers/specs/2026-08-19-venue-semantics-and-venue-map-image-design.md`

---

### Task 1: Extract `parseSemanticDate()` to `ApplePassBuilder`

**Files:**
- Modify: `src/Builders/Apple/ApplePassBuilder.php`
- Modify: `src/Builders/Apple/BoardingPassBuilder.php`

**Interfaces:**
- Consumes: nothing new.
- Produces: `protected function parseSemanticDate(array $semantics, string $key): ?Carbon` on `ApplePassBuilder`. Task 2's `EventTicketPassBuilder::uncompileSemantics()` calls this directly (it's `protected`, and `EventTicketPassBuilder extends ApplePassBuilder`).

This is a pure move: no new test is written because no new behavior is introduced. Verification is that the full existing suite (which already exercises `BoardingPassBuilder`'s date semantics via `AirlinePassBuilder`) still passes identically.

- [ ] **Step 1: Add the method to `ApplePassBuilder`**

In `src/Builders/Apple/ApplePassBuilder.php`, find `uncompileSemantics()`:

```php
    protected function uncompileSemantics(): void
    {
        $semantics = $this->data['semantics'] ?? [];

        $this->totalPrice = empty($semantics['totalPrice'])
            ? null
            : Price::fromArray($semantics['totalPrice']);

        $this->wifiDetails = empty($semantics['wifiAccess'])
            ? null
            : collect($semantics['wifiAccess'])->map(fn (array $wifi) => WifiNetwork::fromArray($wifi));
    }
```

Immediately after its closing brace (and before `uncompileContent()`, which follows it), add:

```php

    /** @param  array<string, mixed>  $semantics */
    protected function parseSemanticDate(array $semantics, string $key): ?Carbon
    {
        if (empty($semantics[$key])) {
            return null;
        }

        return Carbon::parse($semantics[$key]);
    }
```

`Illuminate\Support\Carbon` is already imported in this file — no new import needed.

- [ ] **Step 2: Remove the now-duplicate private method from `BoardingPassBuilder`**

In `src/Builders/Apple/BoardingPassBuilder.php`, find and delete this method (it currently sits right after `uncompileSemantics()`, near the end of the class):

```php
    /** @param  array<string, mixed>  $semantics */
    private function parseSemanticDate(array $semantics, string $key): ?Carbon
    {
        if (empty($semantics[$key])) {
            return null;
        }

        return Carbon::parse($semantics[$key]);
    }
```

Delete only this method — leave everything else in the file (including every call site like `$this->parseSemanticDate($semantics, 'currentArrivalDate')` inside `uncompileSemantics()`) untouched. Those calls now resolve to the inherited `protected` method on `ApplePassBuilder` instead, with identical behavior. `Illuminate\Support\Carbon` stays imported in this file since `BoardingPassBuilder` still declares several `?Carbon` properties.

- [ ] **Step 3: Run the full test suite to confirm no behavior changed**

Run: `vendor/bin/pest`
Expected: PASS — same pass/fail counts as before this change.

- [ ] **Step 4: Commit**

```bash
git add src/Builders/Apple/ApplePassBuilder.php src/Builders/Apple/BoardingPassBuilder.php
git commit -m "Extract parseSemanticDate helper to ApplePassBuilder"
```

---

### Task 2: Venue semantic tags on `EventTicketPassBuilder`

**Files:**
- Modify: `src/Builders/Apple/EventTicketPassBuilder.php`
- Test: `tests/Builders/Apple/EventTicketPassBuilderTest.php`

**Interfaces:**
- Consumes: `ApplePassBuilder::parseSemanticDate(array $semantics, string $key): ?Carbon` from Task 1.
- Produces: 16 new setters on `EventTicketPassBuilder` — `setVenueName(string): self`, `setVenueLocation(Location): self`, `setVenueEntrance(string): self`, `setVenueEntranceDoor(string): self`, `setVenueEntranceGate(string): self`, `setVenueEntrancePortal(string): self`, `setVenuePhoneNumber(string): self`, `setVenueRoom(string): self`, `setVenueRegionName(string): self`, `setVenueOpenDate(Carbon): self`, `setVenueCloseDate(Carbon): self`, `setVenueDoorsOpenDate(Carbon): self`, `setVenueGatesOpenDate(Carbon): self`, `setVenueFanZoneOpenDate(Carbon): self`, `setVenueBoxOfficeOpenDate(Carbon): self`, `setVenueParkingLotsOpenDate(Carbon): self`. Task 3 does not depend on these (it only adds image methods to the same file) but must not remove or collide with them.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/EventTicketPassBuilderTest.php`. First, add two new imports at the top of the file (alongside the existing `use` statements):

```php
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
```

Then add these three tests, after the existing `'has a name'` test (at the end of the file):

```php
it('compiles venue semantics into the semantics payload', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueName('Amsterdam ArenA')
        ->setVenueLocation(Location::make(52.3143, 4.9416))
        ->setVenueEntrance('Gate A')
        ->setVenueEntranceDoor('Door 3')
        ->setVenueEntranceGate('Gate A')
        ->setVenueEntrancePortal('Portal 1')
        ->setVenuePhoneNumber('+31 20 311 1333')
        ->setVenueRoom('Main Hall')
        ->setVenueRegionName('Amsterdam')
        ->setVenueOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueCloseDate(Carbon::parse('2026-08-19T23:00:00+00:00'))
        ->setVenueDoorsOpenDate(Carbon::parse('2026-08-19T18:30:00+00:00'))
        ->setVenueGatesOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->setVenueFanZoneOpenDate(Carbon::parse('2026-08-19T17:00:00+00:00'))
        ->setVenueBoxOfficeOpenDate(Carbon::parse('2026-08-19T16:00:00+00:00'))
        ->setVenueParkingLotsOpenDate(Carbon::parse('2026-08-19T15:00:00+00:00'))
        ->data();

    expect($compiledData['semantics'])->toMatchArray([
        'venueName' => 'Amsterdam ArenA',
        'venueEntrance' => 'Gate A',
        'venueEntranceDoor' => 'Door 3',
        'venueEntranceGate' => 'Gate A',
        'venueEntrancePortal' => 'Portal 1',
        'venuePhoneNumber' => '+31 20 311 1333',
        'venueRoom' => 'Main Hall',
        'venueRegionName' => 'Amsterdam',
        'venueOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueCloseDate' => '2026-08-19T23:00:00+00:00',
        'venueDoorsOpenDate' => '2026-08-19T18:30:00+00:00',
        'venueGatesOpenDate' => '2026-08-19T18:00:00+00:00',
        'venueFanZoneOpenDate' => '2026-08-19T17:00:00+00:00',
        'venueBoxOfficeOpenDate' => '2026-08-19T16:00:00+00:00',
        'venueParkingLotsOpenDate' => '2026-08-19T15:00:00+00:00',
    ]);
    expect($compiledData['semantics']['venueLocation'])->toMatchArray([
        'latitude' => 52.3143,
        'longitude' => 4.9416,
    ]);
});

it('omits venue semantics when none are set', function () {
    $compiledData = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('semantics');
});

it('round-trips venue semantics through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueName('Amsterdam ArenA')
        ->setVenueLocation(Location::make(52.3143, 4.9416))
        ->setVenueOpenDate(Carbon::parse('2026-08-19T18:00:00+00:00'))
        ->save();

    $hydratedData = $model->builder()->data();

    expect($hydratedData['semantics']['venueName'])->toBe('Amsterdam ArenA');
    expect($hydratedData['semantics']['venueLocation'])->toMatchArray([
        'latitude' => 52.3143,
        'longitude' => 4.9416,
    ]);
    expect($hydratedData['semantics']['venueOpenDate'])->toBe('2026-08-19T18:00:00+00:00');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::setVenueName()`.

- [ ] **Step 3: Add the properties, setters, and semantics compile/uncompile overrides**

Replace the full contents of `src/Builders/Apple/EventTicketPassBuilder.php` with:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::EventTicket;

    protected ?array $preferredStyleSchemes = null;

    protected ?string $venueName = null;

    protected ?Location $venueLocation = null;

    protected ?string $venueEntrance = null;

    protected ?string $venueEntranceDoor = null;

    protected ?string $venueEntranceGate = null;

    protected ?string $venueEntrancePortal = null;

    protected ?string $venuePhoneNumber = null;

    protected ?string $venueRoom = null;

    protected ?string $venueRegionName = null;

    protected ?Carbon $venueOpenDate = null;

    protected ?Carbon $venueCloseDate = null;

    protected ?Carbon $venueDoorsOpenDate = null;

    protected ?Carbon $venueGatesOpenDate = null;

    protected ?Carbon $venueFanZoneOpenDate = null;

    protected ?Carbon $venueBoxOfficeOpenDate = null;

    protected ?Carbon $venueParkingLotsOpenDate = null;

    protected static function validator(): ApplePassValidator
    {
        return new EventTicketApplePassValidator;
    }

    public function usePosterLayout(): self
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

        return $this;
    }

    /** The full name of the venue. */
    public function setVenueName(string $venueName): self
    {
        $this->venueName = $venueName;

        return $this;
    }

    /** An object that represents the geographic coordinates of the venue. */
    public function setVenueLocation(Location $venueLocation): self
    {
        $this->venueLocation = $venueLocation;

        return $this;
    }

    /** The full name of the entrance, such as "Gate A", to use to gain access to the ticketed event. */
    public function setVenueEntrance(string $venueEntrance): self
    {
        $this->venueEntrance = $venueEntrance;

        return $this;
    }

    /** The venue entrance door. */
    public function setVenueEntranceDoor(string $venueEntranceDoor): self
    {
        $this->venueEntranceDoor = $venueEntranceDoor;

        return $this;
    }

    /** The venue entrance gate. */
    public function setVenueEntranceGate(string $venueEntranceGate): self
    {
        $this->venueEntranceGate = $venueEntranceGate;

        return $this;
    }

    /** The venue entrance portal. */
    public function setVenueEntrancePortal(string $venueEntrancePortal): self
    {
        $this->venueEntrancePortal = $venueEntrancePortal;

        return $this;
    }

    /** The phone number for inquiries about the venue's ticketed event. */
    public function setVenuePhoneNumber(string $venuePhoneNumber): self
    {
        $this->venuePhoneNumber = $venuePhoneNumber;

        return $this;
    }

    /** The full name of the room where the ticketed event is to take place. */
    public function setVenueRoom(string $venueRoom): self
    {
        $this->venueRoom = $venueRoom;

        return $this;
    }

    /** The name of the city or hosting region of the venue. */
    public function setVenueRegionName(string $venueRegionName): self
    {
        $this->venueRegionName = $venueRegionName;

        return $this;
    }

    /** The date when the venue opens. Use this if none of the more specific venue open tags apply. */
    public function setVenueOpenDate(Carbon $venueOpenDate): self
    {
        $this->venueOpenDate = $venueOpenDate;

        return $this;
    }

    /** The date when the venue closes. */
    public function setVenueCloseDate(Carbon $venueCloseDate): self
    {
        $this->venueCloseDate = $venueCloseDate;

        return $this;
    }

    /** The date the doors to the venue open. */
    public function setVenueDoorsOpenDate(Carbon $venueDoorsOpenDate): self
    {
        $this->venueDoorsOpenDate = $venueDoorsOpenDate;

        return $this;
    }

    /** The date the gates to the venue open. */
    public function setVenueGatesOpenDate(Carbon $venueGatesOpenDate): self
    {
        $this->venueGatesOpenDate = $venueGatesOpenDate;

        return $this;
    }

    /** The date the fan zone opens. */
    public function setVenueFanZoneOpenDate(Carbon $venueFanZoneOpenDate): self
    {
        $this->venueFanZoneOpenDate = $venueFanZoneOpenDate;

        return $this;
    }

    /** The date the box office opens. */
    public function setVenueBoxOfficeOpenDate(Carbon $venueBoxOfficeOpenDate): self
    {
        $this->venueBoxOfficeOpenDate = $venueBoxOfficeOpenDate;

        return $this;
    }

    /** The date the parking lots open. */
    public function setVenueParkingLotsOpenDate(Carbon $venueParkingLotsOpenDate): self
    {
        $this->venueParkingLotsOpenDate = $venueParkingLotsOpenDate;

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

    protected function compileSemantics(): array
    {
        return array_merge(
            parent::compileSemantics(),
            array_filter([
                'venueName' => $this->venueName,
                'venueLocation' => $this->venueLocation?->toArray(),
                'venueEntrance' => $this->venueEntrance,
                'venueEntranceDoor' => $this->venueEntranceDoor,
                'venueEntranceGate' => $this->venueEntranceGate,
                'venueEntrancePortal' => $this->venueEntrancePortal,
                'venuePhoneNumber' => $this->venuePhoneNumber,
                'venueRoom' => $this->venueRoom,
                'venueRegionName' => $this->venueRegionName,
                'venueOpenDate' => $this->venueOpenDate?->toIso8601String(),
                'venueCloseDate' => $this->venueCloseDate?->toIso8601String(),
                'venueDoorsOpenDate' => $this->venueDoorsOpenDate?->toIso8601String(),
                'venueGatesOpenDate' => $this->venueGatesOpenDate?->toIso8601String(),
                'venueFanZoneOpenDate' => $this->venueFanZoneOpenDate?->toIso8601String(),
                'venueBoxOfficeOpenDate' => $this->venueBoxOfficeOpenDate?->toIso8601String(),
                'venueParkingLotsOpenDate' => $this->venueParkingLotsOpenDate?->toIso8601String(),
            ]),
        );
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $this->preferredStyleSchemes = $this->data['preferredStyleSchemes'] ?? null;
    }

    protected function uncompileSemantics(): void
    {
        parent::uncompileSemantics();

        $semantics = $this->data['semantics'] ?? [];

        $this->venueName = $semantics['venueName'] ?? null;
        $this->venueLocation = empty($semantics['venueLocation'])
            ? null
            : Location::fromArray($semantics['venueLocation']);
        $this->venueEntrance = $semantics['venueEntrance'] ?? null;
        $this->venueEntranceDoor = $semantics['venueEntranceDoor'] ?? null;
        $this->venueEntranceGate = $semantics['venueEntranceGate'] ?? null;
        $this->venueEntrancePortal = $semantics['venueEntrancePortal'] ?? null;
        $this->venuePhoneNumber = $semantics['venuePhoneNumber'] ?? null;
        $this->venueRoom = $semantics['venueRoom'] ?? null;
        $this->venueRegionName = $semantics['venueRegionName'] ?? null;
        $this->venueOpenDate = $this->parseSemanticDate($semantics, 'venueOpenDate');
        $this->venueCloseDate = $this->parseSemanticDate($semantics, 'venueCloseDate');
        $this->venueDoorsOpenDate = $this->parseSemanticDate($semantics, 'venueDoorsOpenDate');
        $this->venueGatesOpenDate = $this->parseSemanticDate($semantics, 'venueGatesOpenDate');
        $this->venueFanZoneOpenDate = $this->parseSemanticDate($semantics, 'venueFanZoneOpenDate');
        $this->venueBoxOfficeOpenDate = $this->parseSemanticDate($semantics, 'venueBoxOfficeOpenDate');
        $this->venueParkingLotsOpenDate = $this->parseSemanticDate($semantics, 'venueParkingLotsOpenDate');
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php`
Expected: PASS (all tests in the file, including the three new ones).

- [ ] **Step 5: Commit**

```bash
git add src/Builders/Apple/EventTicketPassBuilder.php tests/Builders/Apple/EventTicketPassBuilderTest.php
git commit -m "Add venue semantic tags to EventTicketPassBuilder"
```

---

### Task 3: `venueMap` image on `EventTicketPassBuilder`

**Files:**
- Modify: `src/Builders/Apple/EventTicketPassBuilder.php`
- Test: `tests/Builders/Apple/EventTicketPassBuilderTest.php`
- Test: `tests/Builders/Apple/LocalizationTest.php`

**Interfaces:**
- Consumes: `Spatie\LaravelMobilePass\Builders\Apple\Entities\Image` (constructor and `Image::makeRemote()`, both already exist — same entity `HasArtworkImage` already uses).
- Produces: `setVenueMapImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self`, `setRemoteVenueMapImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self`, `setLocaleVenueMapImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self`, `setRemoteLocaleVenueMapImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self`. Not consumed by any other task.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Builders/Apple/EventTicketPassBuilderTest.php`, after the three venue-semantics tests added in Task 2 (at the end of the file):

```php
it('bundles a venue map image into the generated pass', function () {
    $generatedPass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setVenueMapImage(
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
            getTestSupportPath('images/spatie-thumbnail.png'),
        )
        ->generate();

    $reader = PkPassReader::fromString($generatedPass);

    expect($reader->containsFile('venueMap.png'))->toBeTrue()
        ->and($reader->containsFile('venueMap@2x.png'))->toBeTrue()
        ->and($reader->containsFile('venueMap@3x.png'))->toBeTrue();
});

it('registers a remote venue map image', function () {
    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setRemoteVenueMapImage('https://example.com/pass/venue-map.png')
        ->save();

    expect($pass->images['venueMap'])
        ->toMatchArray([
            'x1Path' => 'https://example.com/pass/venue-map.png',
            'isRemote' => true,
        ]);
});
```

Add to `tests/Builders/Apple/LocalizationTest.php`, directly after the existing `'bundles localized artwork images into event tickets'` test:

```php
it('bundles localized venue map images into event tickets', function () {
    $imagePath = getTestSupportPath('images/spatie-thumbnail.png');

    $pass = EventTicketPassBuilder::make()
        ->setOrganizationName('My Org')
        ->setSerialNumber('123')
        ->setDescription('Test Pass')
        ->setIconImage($imagePath)
        ->setLocaleVenueMapImage('en', $imagePath, $imagePath)
        ->generate();

    $reader = PkPassReader::fromString($pass);

    expect($reader->containsFile('en.lproj/venueMap.png'))->toBeTrue();
    expect($reader->containsFile('en.lproj/venueMap@2x.png'))->toBeTrue();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php tests/Builders/Apple/LocalizationTest.php`
Expected: FAIL — `Call to undefined method Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::setVenueMapImage()`.

- [ ] **Step 3: Add the import and the four methods**

In `src/Builders/Apple/EventTicketPassBuilder.php` (as it stands after Task 2), add one import. The current `use` block reads:

```php
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;
```

Change it to (inserting the `Entities\Image` import, alphabetized between `HasArtworkImage` and `Entities\Location`):

```php
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;
```

Then find the `usePosterLayout()` method:

```php
    public function usePosterLayout(): self
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

        return $this;
    }
```

Immediately after its closing brace (and before `setVenueName()`, which follows it), add:

```php

    public function setVenueMapImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->images['venueMap'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteVenueMapImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->images['venueMap'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setLocaleVenueMapImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->locales[$language]['images']['venueMap'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleVenueMapImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->locales[$language]['images']['venueMap'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Builders/Apple/EventTicketPassBuilderTest.php tests/Builders/Apple/LocalizationTest.php`
Expected: PASS (all tests in both files, including the three new ones).

- [ ] **Step 5: Commit**

```bash
git add src/Builders/Apple/EventTicketPassBuilder.php tests/Builders/Apple/EventTicketPassBuilderTest.php tests/Builders/Apple/LocalizationTest.php
git commit -m "Add venueMap image support to EventTicketPassBuilder"
```

---

### Task 4: Documentation

**Files:**
- Modify: `docs/available-pass-types/event-ticket.md`
- Modify: `docs/basic-usage/adding-images.md`

**Interfaces:**
- Consumes: nothing (documentation only).
- Produces: nothing consumed by later tasks.

- [ ] **Step 1: Mention venue semantics and `setVenueMapImage()` in the event ticket doc**

In `docs/available-pass-types/event-ticket.md`, find this paragraph (added by an earlier task in this branch):

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

Add a new paragraph directly after that code sample (still inside `## Apple`, before `## Google`):

```markdown
Event tickets can also carry venue details via Apple's semantic tags — `setVenueName()`, `setVenueLocation()`, `setVenueEntrance()`, `setVenueEntranceDoor()`, `setVenueEntranceGate()`, `setVenueEntrancePortal()`, `setVenuePhoneNumber()`, `setVenueRoom()`, `setVenueRegionName()`, and a handful of `setVenue*OpenDate()`/`setVenueCloseDate()` methods — plus a `venueMap` image via `setVenueMapImage()`. Apple's Wallet app shows these in the pass's event guide panel:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;

EventTicketPassBuilder::make()
    // ...
    ->setVenueName('Amsterdam ArenA')
    ->setVenueLocation(Location::make(52.3143, 4.9416))
    ->setVenueMapImage(public_path('images/venue-map.png'))
    ->save();
```
```

- [ ] **Step 2: Add a `venueMap` row to the images guide**

In `docs/basic-usage/adding-images.md`, find the `Artwork` row in the "Recommended dimensions" table:

```markdown
| Artwork | Varies (portrait) | Event tickets and generic passes only. Large portrait image for Apple's poster-style layouts. This is a newer image type without a long-settled spec, so check Apple's current Wallet documentation for the exact current dimensions. |
```

Add a new row directly after it (still before the `Footer` row):

```markdown
| Venue map | Varies | Event tickets only. Shown in the pass's event guide panel. This is a newer image type without a long-settled spec, so check Apple's current Wallet documentation for the exact current dimensions. |
```

- [ ] **Step 3: Commit**

```bash
git add docs/available-pass-types/event-ticket.md docs/basic-usage/adding-images.md
git commit -m "Document venue semantics and venueMap image"
```
