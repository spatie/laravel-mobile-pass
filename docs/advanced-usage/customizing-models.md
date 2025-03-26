---
title: Customizing models
weight: 2
---

If you want to customize the behaviour of the models, you can do so by creating a new model that extends the default
model. This way you can add new methods, traits, etc..

Here's how you would enable the `SoftDeletes` trait on a model:

```php
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomMobilePass extends MobilePass
{
    use SoftDeletes;
}
```

You must then update the `mobile-pass` config file to use the new model:

```php
// config/mobile-pass.php

return [
    'models' => [
        'mobile_pass' =>  App\Models\CustomMobilePass::class,
    ],
];
```
