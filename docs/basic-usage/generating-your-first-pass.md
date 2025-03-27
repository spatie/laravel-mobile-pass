---
title: Generating your first pass
weight: 3
---

The package offers [various builder classes](TODO: add link) that you can use to build passes.  These builders all have specialized methods to build their specific passes.

Here's an example of how you can generate a basic boarding pass:

```php
use Spatie\LaravelMobilePass\Builders\AirlinePassBuilder;

$mobilePass = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
    ->setSerialNumber(123456)
    ->setDescription('Hello!')
    ->setHeaderFields(
        FieldContent::make('flight-no')
            ->withLabel('Flight')
            ->withValue('EY066'),
        FieldContent::make('seat')
            ->withLabel('Seat')
            ->withValue('66F')
    )
    ->setPrimaryFields(
        FieldContent::make('departure')
            ->withLabel('Abu Dhabi International')
            ->withValue('ABU'),
        FieldContent::make('destination')
            ->withLabel('London Heathrow')
            ->withValue('LHR'),
    )
    ->setSecondaryFields(
        FieldContent::make('name')
            ->withLabel('Name')
            ->withValue('Dan Johnson'),
        FieldContent::make('gate')
            ->withLabel('Gate')
            ->withValue('D68')
    )
    ->setAuxiliaryFields(
        FieldContent::make('departs')
            ->withLabel('Departs')
            ->withValue(now()->toIso8601String()),
        FieldContent::make('class')
            ->withLabel('Class')
            ->withValue('Economy'),
    )
    ->setIconImage(
        Image::make(
            x1Path: getTestSupportPath('images/your-thumbnail.png')
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
    ));
    ->save();
```

The `save` method will return a newly created `MobilePass` model.

