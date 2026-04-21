---
title: Expiring passes
weight: 8
---

Sometimes a pass shouldn't be valid anymore. A concert has ended, a coupon was redeemed, a gift card is spent. Call `expire()` on the `MobilePass` model and the package handles the platform specifics for you.

```php
$mobilePass->expire();
```

For Apple passes, the package sets `voided=true` and `expirationDate` to the current time, then pushes the update out via APNs. The pass shows up greyed out in the user's Wallet.

For Google passes, the package patches `state=EXPIRED` on the Object. Google propagates that to the user's device and greys out the pass there too.

Either way, the package also sets `expired_at` on the `MobilePass` row, so you can filter expired passes out of your own queries:

```php
MobilePass::whereNull('expired_at')->get();
```

## Passes stay on the device

Neither Apple nor Google lets you yank a pass off a user's device remotely. What you can do is mark it expired, which tells the wallet app to grey it out and demote it from the lock screen. The user deletes it themselves whenever they're ready.

That's a conscious design choice from both platforms. Users keep control over what's on their device.
