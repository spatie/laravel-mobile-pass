---
title: Storing mobile passes
weight: 6
---

Most of the time you don't need to store a mobile pass yourself. The package generates it on the fly when a user tries to download it.

If you do want to keep a copy around, call `generate` on a `MobilePass` model:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePassContent = $mobilePass->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

# Generating a mobile pass without a model

If you don't want a `MobilePass` model at all, you can call `generate` directly on one of the builders instead:

```php
$mobilePassContent = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
//  other calls
    ->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

Going this route skips creating a `MobilePass` model. Just be aware: without that model, you can't push updates to users who have added the pass to their wallet.
