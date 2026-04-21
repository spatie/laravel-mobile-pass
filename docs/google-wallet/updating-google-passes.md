---
title: Updating Google passes
weight: 4
---

To update a Google pass, update its `MobilePass` model. The package notices and pushes the change to Google, which then pushes the update to the user's device. Google takes care of the device notification on our behalf.

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePass = MobilePass::find($id);

$content = $mobilePass->content;
$content['googleObjectPayload']['ticketHolderName'] = 'Dan Johnson Jr.';

$mobilePass->update(['content' => $content]);
```

Once the model is saved, the package dispatches `NotifyGoogleOfPassUpdateAction`, which patches the Object on Google via the Wallet REST API. Google takes it from there.

## Running the push asynchronously

By default, the update push runs synchronously. That means if Google's API is slow, so is your request. For high-traffic apps, you'll want to move this onto a queue.

Set `MOBILE_PASS_QUEUE_CONNECTION` and the package will dispatch the update job on that connection instead.

See [Queueing update pushes](/docs/laravel-mobile-pass/v1/advanced-usage/queueing-update-pushes) for the full setup.

## Customising the update action

If you want to run your own code around the update push (logging, audit trails, retry policies, that sort of thing), extend `NotifyGoogleOfPassUpdateAction` and register your class in the config. The full walkthrough is in [Customizing actions](/docs/laravel-mobile-pass/v1/advanced-usage/customizing-actions).
