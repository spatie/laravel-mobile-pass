# Venue semantics and the venueMap image for EventTicket passes

## Context

Apple's poster-style event ticket layout shows an "event guide" panel with a
venue map and weather forecast. The venue map comes from a `venueMap.png`
asset in the pass bundle, and its content/relevance is driven by Apple's
venue-related semantic tags (`semantics.venue*`) — none of which this
codebase currently models. `EventTicketPassBuilder` has zero venue support
today; only the unrelated Google builder has `venueName`/`venueAddress`.

Verified against Apple's current Wallet Passes documentation
(`developer.apple.com/documentation/walletpasses/semantictags`), there are
16 venue-related semantic tags:

| Tag | Type |
|---|---|
| `venueName` | localizable string |
| `venueLocation` | `SemanticTagType.Location` (geo dict) |
| `venueEntrance` | localizable string |
| `venueEntranceDoor` | localizable string |
| `venueEntranceGate` | localizable string |
| `venueEntrancePortal` | localizable string |
| `venuePhoneNumber` | localizable string |
| `venueRoom` | localizable string |
| `venueRegionName` | localizable string |
| `venueOpenDate` | ISO 8601 date |
| `venueCloseDate` | ISO 8601 date |
| `venueDoorsOpenDate` | ISO 8601 date |
| `venueGatesOpenDate` | ISO 8601 date |
| `venueFanZoneOpenDate` | ISO 8601 date |
| `venueBoxOfficeOpenDate` | ISO 8601 date |
| `venueParkingLotsOpenDate` | ISO 8601 date |

This matches, type for type, patterns already used elsewhere in this
codebase: `BoardingPassBuilder` already has a `departureLocation`
(`Location` entity) and several `Carbon`-backed semantic date fields
(`currentArrivalDate`, `originalDepartureDate`, etc.), each with its own
one-field-one-setter method — there is no free-form "add any semantic tag"
mechanism anywhere in this codebase (confirmed by inspection: every
semantic tag today is a hand-modeled typed property).

## Goals

- One setter per venue semantic tag on `EventTicketPassBuilder`, matching
  `BoardingPassBuilder`'s existing per-field style exactly.
- `venueMap` image support (`setVenueMapImage()` and its remote/locale
  variants) on `EventTicketPassBuilder`, matching the existing image-method
  pattern used for `artwork`/`background`/etc.
- No coupling between the two: no validation requiring venue semantics
  before `venueMap` can be set, or vice versa — same no-enforcement
  decision already made for artwork/poster-layout in this branch. Apple's
  own OS handles the fallback when relevant data is missing.

## Non-goals

- No generic/free-form semantic tag mechanism — out of scope, and this
  task doesn't need one since every venue tag is individually modeled.
- No validator rule changes — `ApplePassValidator::rules()` already
  declares a blanket `'semantics' => []` rule (unlike `eventTicket`/
  `generic`, which have no bare top-level rule and rely entirely on
  per-field dotted rules). Confirmed none of `BoardingPassBuilder`'s ~15
  existing semantic fields have individual validator rules either — they
  already pass through `validated()` wholesale under the blanket rule. The
  same applies to the new venue fields with zero validator changes needed.
- No changes to `GenericPassBuilder` or any other pass type — venue
  semantics and `venueMap` are event-ticket-only, per Apple's spec.

## Design

### Extract `parseSemanticDate()` to `ApplePassBuilder`

`BoardingPassBuilder` has a `private function parseSemanticDate(array
$semantics, string $key): ?Carbon` used by its own date-typed semantic
fields. `EventTicketPassBuilder` needs the identical logic for its 6 venue
date fields. Rather than duplicating it (the same call made for
`makeFieldContent()` earlier in this branch), extract it to
`ApplePassBuilder` as `protected`, and have `BoardingPassBuilder` call the
inherited version instead of its own private copy. Pure refactor — no
behavior change, `BoardingPassBuilder`'s existing tests must keep passing
unmodified.

```php
protected function parseSemanticDate(array $semantics, string $key): ?Carbon
{
    if (empty($semantics[$key])) {
        return null;
    }

    return Carbon::parse($semantics[$key]);
}
```

### Venue semantic properties and setters on `EventTicketPassBuilder`

16 new properties, mirroring `BoardingPassBuilder`'s existing property
list style:

```php
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
```

16 setters, one per field, matching `BoardingPassBuilder`'s doc-comment +
simple-assignment style (e.g. `setDepartureLocation(Location
$departureLocation): self`). Example for the string, `Location`, and
`Carbon` shapes:

```php
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

/** The date when the venue opens. Use this if none of the more specific venue open tags apply. */
public function setVenueOpenDate(Carbon $venueOpenDate): self
{
    $this->venueOpenDate = $venueOpenDate;

    return $this;
}
```

The remaining 13 setters follow the identical shape, one per property,
each with its own one-line doc comment taken from Apple's published
description (see the table above).

### `compileSemantics()` / `uncompileSemantics()` overrides

`EventTicketPassBuilder` currently has no semantics overrides at all. Add
both, mirroring `BoardingPassBuilder`'s pattern exactly:

```php
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
```

`compileSemantics()`'s override signature narrows the base's `?array`
return type to `array`, matching `BoardingPassBuilder`'s existing
covariant override.

### `venueMap` image methods

Four methods directly on `EventTicketPassBuilder` (no trait — `venueMap`
is event-ticket-only, single-owner, matching how `setFooterImage()` lives
directly on `BoardingPassBuilder` rather than in a shared trait):

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

The `venueMap` key (camelCase, matching Apple's required filename) is
bundled automatically by the existing generic `addImagesToFile()`/
`addLocaleDataToPass()` loops in `ApplePassBuilder` — no changes needed
there, same mechanism already proven for `artwork`.

## Testing

- `tests/Builders/Apple/EventTicketPassBuilderTest.php`:
  - Setting all 16 venue semantic setters produces the corresponding
    `semantics.venue*` keys in `data()`, with `venueLocation` compiled via
    `Location::toArray()` and the 6 date fields compiled as ISO-8601
    strings.
  - Venue semantics are absent from `data()['semantics']` when none are
    set.
  - Venue semantics round-trip through `save()` → `hydrate()` →
    `data()` unchanged (covers `Location`/`Carbon` reconstruction via
    `uncompileSemantics()`).
  - `setVenueMapImage()` bundles `venueMap.png`/`@2x`/`@3x` into the
    generated pass (mirrors the existing artwork bundling test).
  - `setRemoteVenueMapImage()` registers correctly on the saved model
    (mirrors the existing remote artwork test).
- `tests/Builders/Apple/LocalizationTest.php`: one test bundling a
  per-locale `venueMap` image (mirrors the existing locale artwork test).
- `tests/Builders/Apple/AirlinePassBuilderTest.php` (or wherever
  `BoardingPassBuilder`'s semantic date fields are exercised): existing
  tests must keep passing unmodified after the `parseSemanticDate()`
  extraction — no new test needed for that refactor itself, same
  precedent as the `makeFieldContent()` extraction earlier in this
  branch.

## Documentation

- `docs/available-pass-types/event-ticket.md`: mention the venue semantic
  setters and `setVenueMapImage()` in the Apple section.
- `docs/basic-usage/adding-images.md`: add a `venueMap` row to the
  "Recommended dimensions" table (event tickets only). No specific pixel
  figure could be verified against Apple's current documentation during
  this research pass, so follow the same honest-placeholder approach
  already used for the `artwork` row in this same file ("Varies", pointing
  readers to Apple's current docs) rather than asserting an unverified
  number.
