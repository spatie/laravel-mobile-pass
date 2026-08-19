# Poster layout activation for EventTicket and Generic passes

## Context

A prior branch (merged into this one) added `setArtworkImage()` and friends,
letting callers attach the `artwork` image Apple's poster-style pass layouts
render. That branch deliberately stopped short of actually activating the
poster layout in Wallet — the artwork image was bundled into the `.pkpass`
but nothing told Wallet to use it.

Apple's two poster layouts activate in genuinely different ways:

- **`posterEventTicket`** (event tickets): activated via a top-level
  `preferredStyleSchemes` array on the pass (`["posterEventTicket",
  "eventTicket"]`). Wallet validates the pass against each scheme in order
  and falls back to the plain `eventTicket` rendering if the pass doesn't
  qualify — confirmed against Apple's own current Wallet Passes
  documentation (`preferredStyleSchemes`: "An array of schemes to validate
  the pass with... falling back to the designed type if validation fails
  for all the provided schemes"). No separate field set is required —
  Wallet reuses the existing `eventTicket.*` fields.
- **`posterGeneric`** (generic passes): activated by the mere presence of a
  *separate* top-level `posterGeneric` object, with its own independent
  field sets (`headerFields`, `primaryFields`, `footerFields`, `backFields`)
  — distinct from the existing `generic.*` fields. Apple's own guidance is
  to ship both the `generic` and `posterGeneric` blocks together, so
  pre-iOS-27 devices still render the classic layout while newer devices
  get the poster. There is no `preferredStyleSchemes` involved for generic
  passes.

This spec covers activating both, plus the validator changes needed so the
new keys survive `ApplePassValidator::validate()` (which silently strips
any key absent from `rules()` — the same class of bug the previous
branch's final review caught for the artwork image).

## Goals

- `EventTicketPassBuilder::usePosterLayout(): self` — sets
  `preferredStyleSchemes = ['posterEventTicket', 'eventTicket']`.
- `GenericPassBuilder::addPosterHeaderField()` /
  `addPosterPrimaryField()` / `addPosterFooterField()` /
  `addPosterBackField()` — populate a second, independent field set,
  serialized under a new top-level `posterGeneric` key. Adding any one of
  these is what makes `posterGeneric` appear in `pass.json` at all; there
  is no separate toggle for Generic passes.
- Both new top-level keys (`preferredStyleSchemes`, `posterGeneric`)
  survive their respective validators and round-trip correctly through
  `save()` → `hydrate()`.
- Extract the field-building logic shared between `addField()` and the new
  `addPoster*Field()` family into one helper, rather than duplicating it.
- Update documentation for both pass types, and replace the "activation
  not supported yet" caveat in `adding-images.md` with a note that artwork
  should be set before enabling poster mode.

## Non-goals

- No validation or exception requiring an artwork image before poster mode
  is enabled. A poster-enabled pass with no artwork is still a *valid*
  pass — Wallet just falls back to the classic layout — so this is
  documentation guidance only, not an enforced requirement. (Decided
  explicitly during brainstorming: this would be the first place in the
  codebase where a missing image blocks pass generation, which doesn't fit
  the existing "document the requirement, don't enforce it" pattern used
  for every other image type.)
- No raw `setPreferredStyleSchemes(array $schemes)` escape hatch — only
  the semantic `usePosterLayout()`.
- No `purchaseParkingURL` or other poster-event-ticket-specific top-level
  keys beyond `preferredStyleSchemes` — out of scope, not requested.
- No changes to the shared `generic.*` / `eventTicket.*` field sets or
  their existing `add*Field()` methods beyond the internal
  `makeFieldContent()` extraction (behavior-preserving).

## Design

### `EventTicketPassBuilder`

New property and method:

```php
protected ?array $preferredStyleSchemes = null;

public function usePosterLayout(): self
{
    $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

    return $this;
}
```

`compileData()` adds the key alongside the existing `eventTicket` block:

```php
protected function compileData(): array
{
    return array_merge(
        parent::compileData(),
        [
            'eventTicket' => array_filter([...unchanged...]),
            'preferredStyleSchemes' => $this->preferredStyleSchemes,
        ],
    );
}
```

`data()`'s existing outer `array_filter(..., fn ($value) => ! empty($value))`
(in `ApplePassBuilder::data()`) already strips `preferredStyleSchemes` when
it's `null` — no change needed there. This is the same mechanism that
already governs whether `eventTicket` itself appears.

New `uncompileContent()` override for round-tripping:

```php
protected function uncompileContent(): void
{
    parent::uncompileContent();

    $this->preferredStyleSchemes = $this->data['preferredStyleSchemes'] ?? null;
}
```

### `EventTicketApplePassValidator`

Add one rule so the key survives `validated()`:

```php
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
```

### `ApplePassBuilder`: extract `makeFieldContent()`

`addField()` currently builds a `FieldContent` inline before assigning it to
whichever `FieldType`-named collection property. Extract that construction
into a reusable protected helper so the new poster methods (below) can
reuse it without duplicating the label-defaulting / change-message /
date-style / time-style logic:

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

This is a pure extract-method refactor — `addField()`'s behavior and public
signature are unchanged; existing tests must continue to pass unmodified.

### `GenericPassBuilder`

Four new properties and four new methods, mirroring the existing
`addHeaderField()` family's signature shape:

```php
protected ?Collection $posterHeaderFields = null;

protected ?Collection $posterPrimaryFields = null;

protected ?Collection $posterFooterFields = null;

protected ?Collection $posterBackFields = null;

public function addPosterHeaderField(
    string $key,
    string $value,
    ?string $label = null,
    ?string $changeMessage = null,
    ?DateType $dateStyle = null,
    ?TimeStyleType $timeStyle = null,
    ?bool $showDateAsRelative = null,
): self {
    $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

    $this->posterHeaderFields ??= collect();
    $this->posterHeaderFields[$key] = $field;

    return $this;
}
```

`addPosterPrimaryField()`, `addPosterFooterField()`, and
`addPosterBackField()` follow the identical shape, each targeting their own
property (`posterPrimaryFields`, `posterFooterFields`, `posterBackFields`).

`compileData()` adds the `posterGeneric` block:

```php
protected function compileData(): array
{
    return array_merge(
        parent::compileData(),
        [
            'generic' => array_filter([...unchanged...]),
            'posterGeneric' => array_filter([
                'headerFields' => $this->posterHeaderFields?->values()->toArray(),
                'primaryFields' => $this->posterPrimaryFields?->values()->toArray(),
                'footerFields' => $this->posterFooterFields?->values()->toArray(),
                'backFields' => $this->posterBackFields?->values()->toArray(),
            ]),
        ],
    );
}
```

Same stripping mechanism as `eventTicket`/`generic` themselves: if no
`addPoster*Field()` call was ever made, all four collections stay `null`,
`array_filter` empties the `posterGeneric` array, and `data()`'s outer
filter drops the key entirely — no separate enable/disable flag needed.

New `uncompileContent()` override for round-tripping:

```php
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
```

### `GenericApplePassValidator`

```php
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
```

## Testing

- `tests/Builders/Apple/EventTicketPassBuilderTest.php`:
  - `usePosterLayout()` results in `data()['preferredStyleSchemes'] ===
    ['posterEventTicket', 'eventTicket']`.
  - Without calling `usePosterLayout()`, `data()` has no
    `preferredStyleSchemes` key.
  - `preferredStyleSchemes` round-trips through `save()` → `hydrate()` →
    `data()` unchanged.
- `tests/Builders/Apple/GenericPassBuilderTest.php`:
  - Calling all four `addPoster*Field()` methods produces a `posterGeneric`
    block in `data()` with the right nested `headerFields` /
    `primaryFields` / `footerFields` / `backFields` arrays and field
    content (key/value/label).
  - Without calling any `addPoster*Field()` method, `data()` has no
    `posterGeneric` key.
  - `posterGeneric` fields round-trip through `save()` → `hydrate()` →
    `data()` unchanged.
- Existing `addField()`/`addHeaderField()`/etc. tests (across all pass
  types) must keep passing unmodified — the `makeFieldContent()` extraction
  is behavior-preserving.
- No `LocalizationTest.php` changes — poster fields aren't locale-scoped
  objects, matching every other field set in this codebase.

## Documentation

- `docs/available-pass-types/event-ticket.md`: mention `usePosterLayout()`
  in the Apple section.
- `docs/available-pass-types/generic.md`: mention the `addPoster*Field()`
  family and the `posterGeneric` block in the Apple section.
- `docs/basic-usage/adding-images.md`: replace the existing "poster
  activation isn't supported by this package yet" sentence (added by the
  prior branch) with a note that artwork should be set *before* enabling
  poster mode (`usePosterLayout()` / any `addPoster*Field()` call), since
  Wallet silently falls back to the classic layout when no artwork is
  present — this is now supported, so the caveat changes from "not
  possible yet" to "here's the right order to call things in."
