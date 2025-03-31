---
title: Using passes as mail attachments
weight: 7
---

In your mail templates, you can attach passes to your emails. This is useful when you want to send a pass to a user after a purchase or when you want to send a pass to a user after they have signed up for a service.

To attach a pass to an email, you can simply return it in the `attachments` method of your Mailable class. Here's an example:

```php
class OrderShipped extends Mailable
{
    public MobilePass $pass;

    public function __construct(MobilePass $pass)
    {
        $this->pass = $pass;
    }

    public function attachments()
    {
        return [
            $this->pass,
        ];
    }
}
```

By default, the pass will be attached as a `pass.pkpass` file. If you want to change the filename, you can do so specify the download name when creating the pass:

```php
$mobilePass = AirlinePassBuilder::make()
    // this will result as the attachment name being set to 'boarding-pass-john-doe-to-london.pkpass'
    ->setDownloadName('boarding-pass-john-doe-to-london');
    ->setOrganisationName('My organisation')
    -> ... // other pass properties
    ->save();
```
