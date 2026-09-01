# Changelog

All notable changes to `laravel-mobile-pass` will be documented in this file.

## 1.11.0 - 2026-09-01

### What's Changed

* feat: add remaining top-level Apple pass.json fields (poster + boarding + general) by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/69

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.10.0...1.11.0

## 1.10.0 - 2026-09-01

### What's Changed

* fix/feat: seat semantic tag wire keys by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/65
* fix: include nameSuffix when serializing PersonName by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/66
* feat: add Pass.RelevantDates support to Apple passes by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/67
* feat: apple pass personalization by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/68

Apple passes can now declare several relevance windows with `addRelevantDate()` and `addRelevantDateInterval()`. Each single moment is written to both `relevantDate` and `date` so Wallet picks it up on iOS 18 as well as iOS 26, and the deprecated top-level `relevantDate` is derived from the first window so passes still surface on iOS 17 and earlier.

Pass personalization adds a new table. Existing installs should publish and run the new migration:

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate


```
**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.9.0...1.10.0

## 1.9.0 - 2026-08-26

### What's Changed

* Added support for PosterEventTicket and PosterGeneric, via new fields + semantic tags. Added missing semantic tags to BoardingPassBuilder. Created TrainPassBuilder with its own semantic tags. by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/61
* feat: add Code39, Codabar, and Ean13 barcode types by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/63
* feat: add sharingProhibited support to Apple passes by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/62
* feat: Apple multiple barcodes by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/64

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.8.1...1.9.0

## 1.8.1 - 2026-08-07

### What's Changed

* Keep falsy scalar values like "0" in Apple entity serialization by @matthias-deschoenmacker in https://github.com/spatie/laravel-mobile-pass/pull/59

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.8.0...1.8.1

## 1.8.0 - 2026-08-07

### What's Changed

* Add points labels and secondaryLoyaltyPoints to the loyalty builder by @matthias-deschoenmacker in https://github.com/spatie/laravel-mobile-pass/pull/60

### New Contributors

* @matthias-deschoenmacker made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/60

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.7.1...1.8.0

## 1.7.1 - 2026-07-16

### What's Changed

* Apple wallet i18n loc support by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/58

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.7.0...1.7.1

## 1.7.0 - 2026-07-16

### What's Changed

* Google wallet i18n support by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/56

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.6.0...1.7.0

## 1.6.0 - 2026-07-15

### What's Changed

* Add link, text and image modules to Google objects by @lasseeee in https://github.com/spatie/laravel-mobile-pass/pull/53

### New Contributors

* @lasseeee made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/53

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.5.0...1.6.0

## 1.5.0 - 2026-07-13

### What's Changed

* Bump actions/checkout from 4 to 7 by @dependabot[bot] in https://github.com/spatie/laravel-mobile-pass/pull/51
* Add iBeacon relevance support for Apple Wallet passes by @manfield in https://github.com/spatie/laravel-mobile-pass/pull/52

### New Contributors

* @manfield made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/52

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.4.0...1.5.0

## 1.4.0 - 2026-06-19

### What's new

Apple pass builders can now set a background image, matching what the docs and Apple's pass spec describe for event tickets. Previously only `setStripImage` and `setThumbnailImage` were available.

```php
EventTicketPassBuilder::make()
    // ...
    ->setBackgroundImage(public_path('images/background.png'))
    ->save();










```
A remote variant, `setRemoteBackgroundImage()`, is available as well. The image (along with its @2x and @3x densities) is bundled into the generated `.pkpass`.

Implemented in #50, closes #49.

## 1.3.0 - 2026-06-16

### What's new

Google Wallet pass classes now support the shared building blocks that the Google Pay & Wallet console exposes: geographic locations, the links module (the "main page URL" and any other URIs), text modules, and image modules.

These are available on every class type (`GenericPassClass`, `LoyaltyPassClass`, `OfferPassClass`, `EventTicketPassClass`, `BoardingPassClass`):

```php
EventTicketPassClass::make('beatles-shea-1965')
    ->setEventName('The Beatles | Live at Shea')
    ->addLocation(40.7569, -73.8458)
    ->addLink('https://fabfour.example.com', 'Official site')
    ->addTextModule('Doors', 'Doors open at 18:30')
    ->addImageModule('https://example.com/seating-chart.png', 'seating')
    ->save();











```
Values hydrate back when fetching a class through `find()` or `all()`, readable via `getLocations()`, `getLinks()`, `getTextModules()`, and `getImageModules()`. The change is fully backwards compatible.

Implemented in #48, closes #47.

## 1.2.1 - 2026-05-25

### What's Changed

- Respect the configured `mobile_pass` model in the Apple pass builder and the check-for-updates request, so overriding `mobile-pass.models.mobile_pass` (e.g. to use a custom table) is honored throughout the package by @freekmurze in https://github.com/spatie/laravel-mobile-pass/pull/43

Fixes #42

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.2.0...1.2.1

## 1.2.0 - 2026-05-11

### What's Changed

* Switch Google callback verification to ECv2SigningOnly. by @pmartelletti in https://github.com/spatie/laravel-mobile-pass/pull/41

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.1.0...1.2.0

## 1.1.0 - 2026-05-05

### What's Changed

* Add support for remote images by @chatisk in https://github.com/spatie/laravel-mobile-pass/pull/38

### New Contributors

* @chatisk made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/38

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.5...1.1.0

## 1.0.5 - 2026-05-04

### What's Changed

* Fix #32: route Apple webservice lookups by `pass_serial` by @freekmurze in https://github.com/spatie/laravel-mobile-pass/pull/34
* Add reverse relationship for `MobilePass` model by https://github.com/spatie/laravel-mobile-pass/pull/35
* Add `\$keyType = 'string'` so Apple device can resolve id by @pmartelletti in https://github.com/spatie/laravel-mobile-pass/pull/36

PR #34 includes a required schema migration. See [UPGRADING.md](https://github.com/spatie/laravel-mobile-pass/blob/main/UPGRADING.md) for the paste-in migration.

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.4...1.0.5

## 1.0.4 - 2026-05-01

### What's Changed

* fill timeStyle on re-hydration by @niekbr in https://github.com/spatie/laravel-mobile-pass/pull/31

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.3...1.0.4

## 1.0.3 - 2026-04-29

### What's Changed

* Fix #28: hash certificate contents in cached temp filename by @freekmurze in https://github.com/spatie/laravel-mobile-pass/pull/29

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.2...1.0.3

## 1.0.2 - 2026-04-27

### What's Changed

* Include language to payload of Google EventTicketClass by @niekbr in https://github.com/spatie/laravel-mobile-pass/pull/26
* Update EventTicketPassBuilder.php by @MattiaMarchiorato in https://github.com/spatie/laravel-mobile-pass/pull/24

### New Contributors

* @niekbr made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/26

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.1...1.0.2

## 1.0.1 - 2026-04-24

### What's Changed

* Fix broken links in documentation by @injektion in https://github.com/spatie/laravel-mobile-pass/pull/18
* Update generating-your-first-pass.md by @MattiaMarchiorato in https://github.com/spatie/laravel-mobile-pass/pull/23
* Update Apple Pass.NFC link in documentation by @BrookeDot in https://github.com/spatie/laravel-mobile-pass/pull/22
* Fix: emit backFields for StoreCard and Coupon passes by @Alibaghaee in https://github.com/spatie/laravel-mobile-pass/pull/20

### New Contributors

* @injektion made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/18
* @MattiaMarchiorato made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/23
* @BrookeDot made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/22
* @Alibaghaee made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/20

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/1.0.0...1.0.1

## 1.0.0 - 2026-04-23

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/0.2.0...1.0.0

## 0.2.0 - 2026-04-21

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/compare/0.1.2...0.2.0

## 0.1.2 - 2026-04-21

### Changed

- Building an Apple pass with a non-HTTPS `mobile-pass.apple.webservice.host` now throws `InvalidConfig::webserviceHostMustBeHttps` instead of silently producing a pass that Apple Wallet rejects (Apple requires `webServiceURL` to be HTTPS)

## 0.1.1 - 2026-04-21

### Changed

- Apple `.pkpass` downloads are now served with `Content-Disposition: inline` instead of `attachment`, so Safari opens the Wallet preview directly (other browsers still download the file, as they have no Wallet handler)

## 0.1.0 - 2026-04-21

### Added

- Apple `pass.json` now includes `webServiceURL` derived from `mobile-pass.apple.webservice.host`, so iOS can register devices and receive pass updates
- Google Wallet support: class and object builders for BoardingPass, EventTicket, Loyalty, Offer, and Generic pass types
- `$pass->addToWalletUrl()` unified across Apple and Google
- `$pass->expire()` unified across Apple and Google
- `NotifyGoogleOfPassUpdateAction` automatically PATCHes Google-side objects when `MobilePass` is updated
- Save/remove callback endpoint at `/mobile-pass/google/callbacks` with `GoogleMobilePassSaved` / `GoogleMobilePassRemoved` Laravel events
- `$pass->googleEvents` relation and `$pass->isCurrentlySavedToGoogleWallet()` helper
- `PushPassUpdateJob` queueing opt-in via `MOBILE_PASS_QUEUE_CONNECTION`
- Signed Apple `.pkpass` download route backing `addToWalletUrl()`
- `EventTicketPassBuilder` on the Apple side (previously missing)
- Docs for all the above under `docs/basic-usage/` and `docs/advanced-usage/`

### Changed

- `MobilePass::boot()` is now platform-aware and dispatches a `PushPassUpdateJob` (previously called `NotifyAppleOfPassUpdateAction` synchronously)

### Deferred to v1.1

- Google local-path image hosting (object-level only; currently `Image::fromUrl()` is the supported path)
- Smart Tap NFC fields
- Automatic JWKS fetch for callback signing key verification

## 0.0.1 - 2025-03-26

### What's Changed

* Simplify model by @freekmurze in https://github.com/spatie/laravel-mobile-pass/pull/1

### New Contributors

* @freekmurze made their first contribution in https://github.com/spatie/laravel-mobile-pass/pull/1

**Full Changelog**: https://github.com/spatie/laravel-mobile-pass/commits/0.0.1
