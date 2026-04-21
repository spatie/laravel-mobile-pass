---
title: Expiring passes
weight: 8
---

Every pass has a shelf life. The concert ends, the coupon gets redeemed, the gift card runs dry. When that happens, call `expire()` on the `MobilePass` model and let the package sort out the platform specifics:

```php
$mobilePass->expire();
```

For an Apple pass, the package flips `voided` to `true`, stamps `expirationDate` with the current time, and pushes the update to the device through APNs. Wallet greys the pass out.

For a Google pass, it patches `state=EXPIRED` on the Object. Google fans that out to the user's device, and Google Wallet greys the pass there too.

On top of that, the package stamps `expired_at` on the row itself, so you can filter expired passes out of your own queries without thinking about the platform:

```php
MobilePass::whereNull('expired_at')->get();
```

## Passes stay on the device

One thing worth knowing: neither Apple nor Google lets you yank a pass off a user's device remotely. What you can do is mark it expired. The wallet app greys it out, demotes it from the lock screen, and stops surfacing it in relevant moments. The user gets to decide when to actually delete it.

That's a deliberate design choice on both platforms. Users keep control over what lives on their device, and we just nudge the pass into a "you probably don't need this anymore" state.
