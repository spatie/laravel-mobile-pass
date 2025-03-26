---
title: Customizing actions
weight: 1
---

The core functionality of this package is implemented as a set of action classes. These action classes are registered in the config file. You can customize the actions by extending the action classes and registering the new classes in the config file.

Let's assume that you want to execute some code before the package sends a notification to Apple when a pass is updated. You can create a new action class that extends the `NotifyAppleOfPassUpdateAction` action class and override the `execute` method.

```php
namespace App\Actions;

use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomNotifyAppleOfPassUpdateAction extends NotifyAppleOfPassUpdateAction
{
    public function execute(MobilePass $mobilePass)
    {
        // Your custom code here

        parent::execute($pass);
    }
}
```

After creating the new action class, you need to register it in the `mobile-pass` config file. 

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
