---
title: Available pass types
weight: 3
---

The easiest way to download a pass by calling `download` on a `MobilePass` model and return it from a controller. 

```php
class YourController
{
    public function __invoke()
    {
        $pass = MobilePass::find(1);
        
        return $pass->download();
    }
}
```

This will return a response with the pass file and the correct headers to make it downloadable. The name of the pass will be `pass.pkpass` by default. You can change the name of the pass by passing a string as the first argument to the `download` method.

```php
// in your controller
$passName = "boarding-pass-john-doe-to-london";

// the pass will be downloaded as `boarding-pass-john-doe-to-london.pkpass`
return $pass->download($passName);
```
