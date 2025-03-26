---
title: Updating passes
weight: 4
---

When your user downloads a pass and adds it to their Wallet, Apple will send a request to confirm that the pass has been registered.

The package will handle the incoming request, and store the registration in the `mobile_pass_registrations` table. This registration will be associated the `MobilePass` model that was used to generate the pass that was downloaded by the user.

Here's how you can update a saved pass.

```php
use App\Models\MobilePass;

$mobilePass = $user->firstMobilePass();

TODO: add code to update the pass
```

When a pass gets updated, the package will send a request to Apple to update the pass on the user's device. Apple will then send a push notification to inform the user that the pass has been updated.
