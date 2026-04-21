---
title: Customizing actions
weight: 1
---

The core behaviour of this package is split across a set of action classes, all registered in the config file. You can swap any of them out by extending the default class and pointing the config at your version.

Say you want to run some code right before the package notifies Apple that a pass has been updated. Create a class that extends `NotifyAppleOfPassUpdateAction` and override the `execute` method:

```php
namespace App\Actions;

use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomNotifyAppleOfPassUpdateAction extends NotifyAppleOfPassUpdateAction
{
    public function execute(MobilePass $mobilePass)
    {
        // Your custom code here

        parent::execute($mobilePass);
    }
}
```

Then register your class in the `mobile-pass` config file:

```php
// config/mobile-pass.php

return [
    // other keys

    'actions' => [
        // other actions
    
        'notify_apple_of_pass_update' => \App\Actions\CustomNotifyAppleOfPassUpdateAction::class,
    ],
];
```
