<?php

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('round-trips seats through save and hydrate', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setSeats(Seat::make(
            number: '22',
            row: '8',
            section: 'B12',
            aisle: 'A',
            level: 'Lower Bowl',
            sectionColor: 'rgb(23,187,82)',
        ))
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['semantics']['seats'])->toBe([
        [
            'seatAisle' => 'A',
            'seatLevel' => 'Lower Bowl',
            'seatNumber' => '22',
            'seatRow' => '8',
            'seatSection' => 'B12',
            'seatSectionColor' => 'rgb(23,187,82)',
        ],
    ]);
});

it('hydrates a legacy row that stored seats under the bare keys', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'semantics' => [
                'seats' => [
                    ['number' => '22', 'row' => '8', 'section' => 'B12'],
                ],
            ],
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['semantics']['seats'])->toBe([
        [
            'seatNumber' => '22',
            'seatRow' => '8',
            'seatSection' => 'B12',
        ],
    ]);
});
