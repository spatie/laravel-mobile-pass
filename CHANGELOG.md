# Changelog

All notable changes to `laravel-mobile-pass` will be documented in this file.

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
