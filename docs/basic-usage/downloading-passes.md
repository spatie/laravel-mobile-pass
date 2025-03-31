---
title: Downloading passes
weight: 6
---

The easiest way to download a pass is by returning a `MobilePass` model and return it from a controller. 

```php
class YourController
{
    public function __invoke()
    {
        $pass = MobilePass::find(1);
        
        return $pass;
    }
}
```

This will return a response with the pass file and the correct headers to make it downloadable. The name of the pass will be `pass.pkpass` by default. You can change the name of the pass by specifying a download name when creating the mobile pass.

```php
// Step 1: Create a pass
$mobilePass = AirlinePassBuilder::make()
    ->setDownloadName('boarding-pass-john-doe-to-london');
    ->setOrganisationName('My organisation')
    -> ... // other pass properties
    ->save();
 

// Step 2: in your controller return that mobile pass 


class YourController
{
    public function __invoke()
    {
        $pass = MobilePass::find(1);
        
        // the name of the downloaded pass will be `boarding-pass-john-doe-to-london.pkpass` 
        return $mobilePass;
    }
}

```

You can also specify the download name when returning the pass from a controller.

```php
// in your controller

// the name of the downloaded pass will be `custom-pass-download-name.pkpass` 
$pass->download('custom-pass-download-name');
```
