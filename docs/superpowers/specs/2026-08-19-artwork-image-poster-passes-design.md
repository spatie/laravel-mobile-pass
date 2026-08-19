# Artwork image support for Poster-style passes

## Context

Apple's iOS 18 "poster" event-ticket layout (`posterEventTicket`) and the newer
iOS 27 "Poster Generic" layout (`posterGeneric`) both render pass content over
a large image asset called **`artwork`** (`artwork.png` / `artwork@2x.png` /
`artwork@3x.png`). This is a distinct asset from the existing `strip` image
that the base `ApplePassBuilder` already supports for every pass type.

Only `eventTicket` and `generic` pass types support a poster layout in Apple's
pass schema, so the artwork image methods should be scoped to
`EventTicketPassBuilder` and `GenericPassBuilder`, not exposed on every pass
type.

Activating the poster layout itself (setting `preferredStyleSchemes` on the
pass) is out of scope for this change. This change only adds the ability to
attach and bundle the artwork image(s), local and remote, top-level and
per-locale — mirroring how every other image type (logo, icon, strip,
thumbnail, background) is already supported.

## Goals

- Add `setArtworkImage()`, `setRemoteArtworkImage()`, `setLocaleArtworkImage()`,
  and `setRemoteLocaleArtworkImage()` to `EventTicketPassBuilder` and
  `GenericPassBuilder`.
- Bundle the artwork image(s) into the generated `.pkpass` the same way every
  other image type is bundled today (no special-casing).
- Keep the two pass builders free of duplicated method bodies.

## Non-goals

- Setting `preferredStyleSchemes` / otherwise opting a pass into the poster
  layout.
- Any new validation beyond the existing file-existence check every other
  image type already gets via the `Image` entity.

## Design

### New trait: `HasArtworkImage`

Add `Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage` at
`src/Builders/Apple/Concerns/HasArtworkImage.php`. Both
`EventTicketPassBuilder` and `GenericPassBuilder` `use` this trait.

This is a new `Concerns` namespace under `src/Builders/Apple/` — there isn't
one today, but the pattern (a trait shared by two otherwise-unrelated
builders) doesn't fit cleanly as a method on a common ancestor, since
`EventTicketPassBuilder` and `GenericPassBuilder` share no parent below
`ApplePassBuilder`. This mirrors the existing `src/Models/Concerns` directory
naming convention used elsewhere in the package.

### Methods

Signatures and bodies follow the exact existing pattern used for
`setBackgroundImage` / `setRemoteBackgroundImage` /
`setLocaleBackgroundImage` / `setRemoteLocaleBackgroundImage` in
`ApplePassBuilder`, substituting the `artwork` key:

```php
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

### Bundling

No changes to `ApplePassBuilder::addImagesToFile()` or
`addLocaleDataToPass()` — both already iterate generically over the `images`
array (keyed by asset name) and the per-locale `images` array, writing
`{key}.png`, `{key}@2x.png`, `{key}@3x.png` (and `{lang}.lproj/{key}.png` for
locales). `artwork` is bundled automatically once it's present in those
arrays, exactly like every other image type.

### Error handling

None beyond what already exists: `Image`'s constructor calls
`assertFileExists()` for local paths and skips the check for remote images —
identical behavior to every other image setter.

## Testing

- `tests/Builders/Apple/EventTicketPassBuilderTest.php`: add a test that
  bundles a local artwork image (1x/2x/3x) into the generated pass and
  asserts the files exist in the zip (mirrors the existing "bundles a
  background image" test), and a test that registers a remote artwork image
  and asserts it's stored on the model (mirrors "registers a remote
  background image").
- `tests/Builders/Apple/GenericPassBuilderTest.php`: same two tests, adapted
  to `GenericPassBuilder`.
- `tests/Builders/Apple/LocalizationTest.php`: add a test bundling a
  per-locale artwork image (mirrors the existing locale footer/strip image
  tests), using `EventTicketPassBuilder`.

## Documentation

Apple image setters are documented centrally in
`docs/basic-usage/adding-images.md`, not in the per-pass-type files (those
only show field-related examples). Update that file:

- In the "Apple" section, add `setArtworkImage()` to the sentence listing
  strip/thumbnail/background, noting it's only available on event tickets
  and generic passes (poster layout artwork).
- Add an "Artwork" row to the "Recommended dimensions" table, with a note
  that it's specific to the poster layout on event tickets and generic
  passes. Apple's published poster artwork size will be used for the 1x
  column, mirroring how the existing rows cite Apple's published sizes.
