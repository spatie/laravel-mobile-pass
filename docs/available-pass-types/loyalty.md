---
title: Loyalty card
weight: 5
---

Loyalty cards cover membership programs, frequent-buyer cards, and stamp cards. Apple calls this a store card (`StoreCardPassBuilder`); Google calls it a loyalty pass (`LoyaltyPassBuilder`).

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder;

StoreCardPassBuilder::make()
    ->setOrganizationName('Spatie Rewards')
    ->setSerialNumber('CARD-USER-7842')
    ->setDescription('Spatie Rewards member card')
    ->addField('balance', '1,250', label: 'Points')
    ->addSecondaryField('member', 'Ringo Starr')
    ->addSecondaryField('tier', 'Gold')
    ->save();
```

## Google

Declare the Class once per program (the brand, the colors, the labels), then create an Object per member.

```php
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;

// Once, per program
LoyaltyPassClass::make('spatie-rewards')
    ->setIssuerName('Spatie')
    ->setProgramName('Spatie Rewards')
    ->setProgramLogoUrl('https://cdn.example.com/spatie-logo.png')
    ->setAccountNameLabel('Member')
    ->setAccountIdLabel('Member ID')
    ->setBackgroundColor('#1d72b8')
    ->save();

// Per member
LoyaltyPassBuilder::make()
    ->setClass('spatie-rewards')
    ->setAccountId('USER-7842')
    ->setAccountName('Ringo Starr')
    ->setBalanceString('1,250 points')
    ->save();
```

### Labels and a second value on the card face

`setBalanceLabel()` sets the caption above the balance. Without it, Google falls back to a locale default.

`setSecondaryBalanceString()` (or `setSecondaryBalanceMicros()`) together with `setSecondaryBalanceLabel()` render a second label/value pair next to the balance on the card face. This is the only way to show a second value there. `setAccountName()` is rendered in the details section below the card instead.

```php
LoyaltyPassBuilder::make()
    ->setClass('spatie-rewards')
    ->setAccountId('USER-7842')
    ->setBalanceString('1,250')
    ->setBalanceLabel('Points')
    ->setSecondaryBalanceString('Gold')
    ->setSecondaryBalanceLabel('Tier')
    ->save();
```
