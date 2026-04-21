---
title: Generating your first pass
weight: 2
---

The package offers [various builder classes](/docs/laravel-mobile-pass/v1/basic-usage/available-pass-types) that you can use to build passes. These builders all have specialized methods to build their specific passes.

Here's an example of how you can generate a basic airline boarding pass:

```php
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

$mobilePass = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
    ->setSerialNumber('123456')
    ->setDescription('Hello!')
    ->addHeaderField('flight-no', 'EY066', label: 'Flight')
    ->addHeaderField('seat', '66F')
    ->addPrimaryField('departure', 'ABU', label: 'Abu Dhabi International')
    ->addPrimaryField('destination', 'LHR', label: 'London Heathrow')
    ->addSecondaryField('name', 'Dan Johnson')
    ->addSecondaryField('gate', 'D68')
    ->addAuxiliaryField('departs', now()->toIso8601String())
    ->addAuxiliaryField('class', 'Economy')
    ->setIconImage(
        Image::make(
            x1Path: public_path('images/your-thumbnail.png')
        )
    )

    // Now set the semantic fields.
    ->setDepartureAirportCode('AUH')
    ->setDepartureAirportName('Abu Dhabi Intl')
    ->setDepartureLocationDescription('Abu Dhabi Intl')
    ->setDestinationAirportCode('LHR')
    ->setDestinationAirportName('London Heathrow')
    ->setDestinationLocationDescription('Abu Dhabi Intl')
    ->setSeats(Seat::make(
        number: '66F',
    ))
    ->save();
```

The `save()` method returns a newly created `MobilePass` model.

## Field methods

Every Apple builder exposes the five field zones Apple supports:

- `addHeaderField($key, $value, label: ?, changeMessage: ?)`
- `addPrimaryField($key, $value, label: ?, changeMessage: ?)`
- `addSecondaryField($key, $value, label: ?, changeMessage: ?)`
- `addAuxiliaryField($key, $value, label: ?, changeMessage: ?)`
- `addBackField($key, $value, label: ?, changeMessage: ?)`

The `$key` is a free-form identifier, unique within the pass. It is how you reference the field later when you update its value. The label shown on the pass defaults to a title-cased version of the key. Pass `label:` when you want a different display string. Pass `changeMessage:` when you want the user's device to show a notification whenever that field's value is updated (e.g. `'Your gate has changed to :value'`); the `:value` placeholder is replaced with the new field value.
