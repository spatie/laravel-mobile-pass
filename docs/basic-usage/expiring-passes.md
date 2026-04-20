---
title: Expiring passes
weight: 8
---

Sometimes a pass should no longer be valid. A concert has ended, a coupon was redeemed, a gift card was spent. Call `expire()` on the `MobilePass` model and the package handles the platform specifics for you.

```php
$pass->expire();
```

For Apple passes, the package sets `voided=true` and `expirationDate` to the current time, then pushes an update via APNs. The pass will show up greyed out in the user's Wallet.

For Google passes, the package patches `state=EXPIRED` on the Object. Google propagates that to the user's device and greys out the pass there.

In both cases the package also sets `expired_at` on the `MobilePass` row, so you can filter expired passes out of your app's queries:

```php
MobilePass::whereNull('expired_at')->get();
```

## Passes stay on the device

Neither Apple nor Google lets you remove a pass from a user's device remotely. What you can do is mark it expired, which signals the wallet app to grey it out and demote it from the lock screen. The user can delete it themselves whenever they like.

This is a conscious design choice from both platforms. Users keep control over what's on their device.
