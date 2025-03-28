---
title: Storing mobile passes
weight: 5
---

Typically, you don't need to store mobile passes as they will be generated on the fly when a user tries to download it.

However, if you want to store the pass for later use, you can do so by using the `generate` method on a `MobilePass` model.

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePassContent = $mobilePass->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

# Generating a mobile pass without a model

If you don't want to create `MobilePass` model, you could also call `generate` on one of the mobile pass builders.

```php
$mobilePassContent = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
//  other calls
    ->generate();

file_put_contents('path/to/store/pass.pkpass', $mobilePassContent);
```

The code above will not create a `MobilePass` model. Be aware, that if you create a pass this way that users will not be notified when the pass is updated.
