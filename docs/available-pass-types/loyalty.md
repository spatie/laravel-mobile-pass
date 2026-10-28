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

### Balance types

Google shows a balance as exactly one of a string, an integer, a decimal, or a money amount. Pick the setter that matches. If you call more than one, the last call wins:

- `setBalanceString('1,250 points')`
- `setBalanceInt(1250)`
- `setBalanceDouble(12.5)`
- `setBalanceMoney(12_500_000, 'EUR')`, where the amount is in micros (millionths of the currency unit), so this is €12.50

The `setSecondaryBalance*()` methods take the same types. `setBalanceMicros()` and `setSecondaryBalanceMicros()` are deprecated. Without a currency code they send the value as an integer balance, and with one (`setBalanceMicros(12_500_000, 'EUR')`) they send it as a money balance.

### Labels and a second value on the card face

`setBalanceLabel()` sets the caption above the balance. Without it, Google falls back to a locale default.

`setSecondaryBalanceString()` (or any other `setSecondaryBalance*()` method) together with `setSecondaryBalanceLabel()` render a second label/value pair next to the balance on the card face. This is the only way to show a second value there. `setAccountName()` is rendered in the details section below the card instead.

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
