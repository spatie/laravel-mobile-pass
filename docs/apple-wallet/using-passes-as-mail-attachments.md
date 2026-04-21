---
title: Using passes as mail attachments
weight: 4
---

You can attach passes to the emails you send. That's handy after a purchase, or once someone signs up for something. The pass lands in their inbox ready to add to Wallet.

To attach a pass to an email, just return it from the `attachments` method of your Mailable. Here's an example:

```php
class OrderShipped extends Mailable
{
    public MobilePass $mobilePass;

    public function __construct(MobilePass $mobilePass)
    {
        $this->pass = $mobilePass;
    }

    public function attachments()
    {
        return [
            $this->pass,
        ];
    }
}
```

By default the pass is attached as `pass.pkpass`. To give it a different filename, set the download name on the builder:

```php
$mobilePass = AirlinePassBuilder::make()
    // this will result as the attachment name being set to 'boarding-pass-john-doe-to-london.pkpass'
    ->setDownloadName('boarding-pass-john-doe-to-london');
    ->setOrganisationName('My organisation')
    -> ... // other pass properties
    ->save();
```
