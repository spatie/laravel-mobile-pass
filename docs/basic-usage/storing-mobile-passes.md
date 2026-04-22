---
title: Storing mobile passes
weight: 9
---

Most of the time you don't need to store a mobile pass yourself. The package generates it on the fly when a user downloads it.

Everything on this page is Apple-only. Google Wallet passes aren't files; they live on Google's servers, and users reach them through a `pay.google.com` save URL rather than downloading anything.

If you do want to keep a copy of the `.pkpass` file for an Apple pass, call `generate` on the `MobilePass` model:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePassContent = $mobilePass->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

## Generating a mobile pass without a model

If you don't want a `MobilePass` model at all, you can call `generate` directly on one of the Apple builders instead:

```php
$mobilePassContent = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
//  other calls
    ->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

Going this route skips creating a `MobilePass` model. Just be aware: without that model, you can't push updates to users who have added the pass to their wallet.
