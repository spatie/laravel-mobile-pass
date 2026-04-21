---
title: Generating your first pass
weight: 2
---

The package ships with [a builder for each pass type](/docs/laravel-mobile-pass/v1/available-pass-types/introduction) Apple supports. Each builder has setters specific to the kind of pass you're making.

Here's a basic airline boarding pass:

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
    ->addField('departure', 'ABU', label: 'Abu Dhabi International')
    ->addField('destination', 'LHR', label: 'London Heathrow')
    ->addSecondaryField('name', 'Paul McCartney')
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

Calling `save()` gives you back a freshly created `MobilePass` model.

## Field methods

Every Apple builder exposes the five field zones Apple supports:

- `addHeaderField($key, $value, label: ?, changeMessage: ?)`
- `addField($key, $value, label: ?, changeMessage: ?)`
- `addSecondaryField($key, $value, label: ?, changeMessage: ?)`
- `addAuxiliaryField($key, $value, label: ?, changeMessage: ?)`
- `addBackField($key, $value, label: ?, changeMessage: ?)`

The `$key` is a free-form identifier you pick, unique within the pass. You'll reference it later when you want to update that field's value. The label shown on the pass defaults to a title-cased version of the key, so pass `label:` when you want something different. Pass `changeMessage:` when you want the user's device to show a notification whenever the field's value changes (for example, `'Your gate has changed to :value'`). The `:value` placeholder gets swapped out with the new field value.
