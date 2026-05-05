---
title: Customizing models
weight: 3
---

If you want to change how the models behave, create a new model that extends the default one. From there you can add your own methods, traits, or whatever else you need.

Here's how you'd turn on the `SoftDeletes` trait on a model:

```php
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomMobilePass extends MobilePass
{
    use SoftDeletes;
}
```

Then update the `mobile-pass` config to use your new model:

```php
// config/mobile-pass.php

return [
    'models' => [
        'mobile_pass' =>  App\Models\CustomMobilePass::class,
    ],
];
```
