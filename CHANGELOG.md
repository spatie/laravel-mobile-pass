# Changelog

All notable changes to `laravel-mobile-pass` will be documented in this file.

## Unreleased

### Added

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
