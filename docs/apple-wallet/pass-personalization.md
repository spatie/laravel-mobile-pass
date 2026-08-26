---
title: Pass personalization
weight: 7
---

Apple Wallet lets you collect customer information right from the pass itself. A loyalty card can ask for the member's email address, a rewards pass can collect postal code or phone number, and a membership card can gather multiple fields. This is called pass personalization, and it's a streamlined way to build your customer database without redirecting to your website.

Personalization is Apple-only and NFC-only. The package enforces the NFC requirement: if you configure personalization without NFC, building the pass throws an `InvalidConfig` exception.

## Setting up personalization

Use `setPersonalization()` on your builder, passing a `Personalization` instance created with `Personalization::make()`. Tell it what description to show in Wallet, which fields to ask for, and optionally a terms-and-conditions message:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Personalization;
use Spatie\LaravelMobilePass\Enums\PersonalizationField;

$builder->setPersonalization(
    Personalization::make(
        description: 'Complete your profile to unlock rewards',
        requiredPersonalizationFields: [
            PersonalizationField::Name,
            PersonalizationField::EmailAddress,
        ],
        termsAndConditions: "We'll use your email to send loyalty updates. You can unsubscribe anytime.",
    )
);
```

The four available fields are:

- `PersonalizationField::Name` — the member's name
- `PersonalizationField::PostalCode` — a postal code (e.g. ZIP for US, postcode for UK)
- `PersonalizationField::EmailAddress` — an email address
- `PersonalizationField::PhoneNumber` — a phone number

Personalization always requires NFC. Set it up with `setNfc()`, in either order relative to `setPersonalization()` — the package checks for NFC when the pass is built, not when either method is called:

```php
$builder
    ->setNfc(
        message: 'Tap to earn points',
        encryptionPublicKey: 'your-public-key',
    )
    ->setPersonalization(
        Personalization::make(
            description: 'Complete your profile to unlock rewards',
            requiredPersonalizationFields: [PersonalizationField::EmailAddress],
        )
    );
```

## Personalization logo

Personalization shows a logo in Wallet during the enrollment flow. Provide it with `setPersonalizationLogo()`, just like other images in the pass:

```php
$builder->setPersonalizationLogo(
    x1Path: 'resources/pass-images/personalization-logo@1x.png',
    x2Path: 'resources/pass-images/personalization-logo@2x.png',
    x3Path: 'resources/pass-images/personalization-logo@3x.png',
);
```

Only the `@1x` image is required; `@2x` and `@3x` are optional.

## Handling the personalization endpoint

The `/personalize` web-service endpoint that Wallet calls after the user submits their information is wired up automatically by the package. You don't need to define a route or controller — it's handled behind the scenes.

## Listening for personalization

Once the user submits their information, the package fires the `PassPersonalized` event. This is where you should create the user's account, sync a CRM, send a welcome email, or do anything else your app needs:

```php
namespace App\Listeners;

use Spatie\LaravelMobilePass\Events\PassPersonalized;

class CreateUserAccount
{
    public function handle(PassPersonalized $event): void
    {
        $mobilePass = $event->mobilePass;
        $submittedInfo = $event->submittedInfo; // array of user-entered data

        // Create or update the user
        // Note: field keys depend on what Wallet sends; this example assumes fullName and emailAddress
        $user = User::updateOrCreate(
            ['email' => $submittedInfo['emailAddress'] ?? null],
            [
                'name' => $submittedInfo['fullName'] ?? null,
                'email' => $submittedInfo['emailAddress'] ?? null,
                'phone' => $submittedInfo['phoneNumber'] ?? null,
                'postal_code' => $submittedInfo['postalCode'] ?? null,
            ]
        );

        // Link the mobile pass to the user
        $mobilePass->model()->associate($user)->save();

        // Send a welcome email, trigger onboarding, etc.
    }
}
```

The `$submittedInfo` array is a verbatim passthrough of the `requiredPersonalizationInfo` payload Wallet sends — its structure and key names are controlled by Apple, not this package. The example above uses `fullName` (for `PersonalizationField::Name`) and `emailAddress` (for `PersonalizationField::EmailAddress`); check your pass builder's `requiredPersonalizationFields` to understand which keys will be present in your listener.

Register your listener in `EventServiceProvider` or let Laravel 11+ auto-discover it from `app/Listeners`. See [Events](advanced-usage/events) for listener setup details.

## Why you handle account creation

Apple's spec explicitly places account creation on your server, not the package. The package delivers the submitted information and fires the event; your listener decides whether to create an account, sync an external system, update an existing user, or skip the whole thing. This keeps the package lightweight and lets your app apply your business logic.
