<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Models\BoardingPasses\AirlinePass;

use function Pest\testDirectory;

it('creates_record', function () {
    $pass = AirlinePass::create()
        ->setDescription('Hello!')
        ->addHeaderFields(
            FieldContent::make('flight-no')
                ->withLabel('Flight')
                ->withValue('EY066'),
            FieldContent::make('seat')
                ->withLabel('Seat')
                ->withValue('66F')
        )
        ->addPrimaryFields(
            FieldContent::make('departure')
                ->withLabel('Abu Dhabi International')
                ->withValue('ABU'),
            FieldContent::make('destination')
                ->withLabel('London Heathrow')
                ->withValue('LHR'),
        )
        ->addSecondaryFields(
            FieldContent::make('name')
                ->withLabel('Name')
                ->withValue('Dan Johnson'),
            FieldContent::make('gate')
                ->withLabel('Gate')
                ->withValue('D68')
        )

        ->setIconImage(
            Image::make(
                x1Path: testDirectory('Helpers/Images/spatie-thumbnail.png')
            )
        );

    $pass->save();
    $file = $pass->generate();

    file_put_contents('test.pkpass', $file);
});
