<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::EventTicket;

    /** @var array<int, string>|null */
    protected ?array $preferredStyleSchemes = null;

    protected ?string $venueName = null;

    protected ?Location $venueLocation = null;

    protected ?string $venueEntrance = null;

    protected ?string $venueEntranceDoor = null;

    protected ?string $venueEntranceGate = null;

    protected ?string $venueEntrancePortal = null;

    protected ?string $venuePhoneNumber = null;

    protected ?string $venueRoom = null;

    protected ?string $venueRegionName = null;

    protected ?Carbon $venueOpenDate = null;

    protected ?Carbon $venueCloseDate = null;

    protected ?Carbon $venueDoorsOpenDate = null;

    protected ?Carbon $venueGatesOpenDate = null;

    protected ?Carbon $venueFanZoneOpenDate = null;

    protected ?Carbon $venueBoxOfficeOpenDate = null;

    protected ?Carbon $venueParkingLotsOpenDate = null;

    protected static function validator(): ApplePassValidator
    {
        return new EventTicketApplePassValidator;
    }

    public function usePosterLayout(): static
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

        return $this;
    }

    public function setVenueMapImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['venueMap'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteVenueMapImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['venueMap'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setLocaleVenueMapImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['venueMap'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleVenueMapImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['venueMap'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setVenueName(string $venueName): static
    {
        $this->venueName = $venueName;

        return $this;
    }

    public function setVenueLocation(Location $venueLocation): static
    {
        $this->venueLocation = $venueLocation;

        return $this;
    }

    /** The full name of the entrance, such as "Gate A", to use to gain access to the ticketed event. */
    public function setVenueEntrance(string $venueEntrance): static
    {
        $this->venueEntrance = $venueEntrance;

        return $this;
    }

    public function setVenueEntranceDoor(string $venueEntranceDoor): static
    {
        $this->venueEntranceDoor = $venueEntranceDoor;

        return $this;
    }

    public function setVenueEntranceGate(string $venueEntranceGate): static
    {
        $this->venueEntranceGate = $venueEntranceGate;

        return $this;
    }

    public function setVenueEntrancePortal(string $venueEntrancePortal): static
    {
        $this->venueEntrancePortal = $venueEntrancePortal;

        return $this;
    }

    public function setVenuePhoneNumber(string $venuePhoneNumber): static
    {
        $this->venuePhoneNumber = $venuePhoneNumber;

        return $this;
    }

    public function setVenueRoom(string $venueRoom): static
    {
        $this->venueRoom = $venueRoom;

        return $this;
    }

    public function setVenueRegionName(string $venueRegionName): static
    {
        $this->venueRegionName = $venueRegionName;

        return $this;
    }

    /** The date when the venue opens. Use this if none of the more specific venue open tags apply. */
    public function setVenueOpenDate(Carbon $venueOpenDate): static
    {
        $this->venueOpenDate = $venueOpenDate;

        return $this;
    }

    public function setVenueCloseDate(Carbon $venueCloseDate): static
    {
        $this->venueCloseDate = $venueCloseDate;

        return $this;
    }

    public function setVenueDoorsOpenDate(Carbon $venueDoorsOpenDate): static
    {
        $this->venueDoorsOpenDate = $venueDoorsOpenDate;

        return $this;
    }

    public function setVenueGatesOpenDate(Carbon $venueGatesOpenDate): static
    {
        $this->venueGatesOpenDate = $venueGatesOpenDate;

        return $this;
    }

    public function setVenueFanZoneOpenDate(Carbon $venueFanZoneOpenDate): static
    {
        $this->venueFanZoneOpenDate = $venueFanZoneOpenDate;

        return $this;
    }

    public function setVenueBoxOfficeOpenDate(Carbon $venueBoxOfficeOpenDate): static
    {
        $this->venueBoxOfficeOpenDate = $venueBoxOfficeOpenDate;

        return $this;
    }

    public function setVenueParkingLotsOpenDate(Carbon $venueParkingLotsOpenDate): static
    {
        $this->venueParkingLotsOpenDate = $venueParkingLotsOpenDate;

        return $this;
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'eventTicket' => array_filter([
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                    'backFields' => $this->backFields?->values()->toArray(),
                ]),
                'preferredStyleSchemes' => $this->preferredStyleSchemes,
            ],
        );
    }

    protected function compileSemantics(): array
    {
        return array_merge(
            parent::compileSemantics(),
            array_filter([
                'venueName' => $this->venueName,
                'venueLocation' => $this->compileVenueLocation(),
                'venueEntrance' => $this->venueEntrance,
                'venueEntranceDoor' => $this->venueEntranceDoor,
                'venueEntranceGate' => $this->venueEntranceGate,
                'venueEntrancePortal' => $this->venueEntrancePortal,
                'venuePhoneNumber' => $this->venuePhoneNumber,
                'venueRoom' => $this->venueRoom,
                'venueRegionName' => $this->venueRegionName,
                'venueOpenDate' => $this->venueOpenDate?->toIso8601String(),
                'venueCloseDate' => $this->venueCloseDate?->toIso8601String(),
                'venueDoorsOpenDate' => $this->venueDoorsOpenDate?->toIso8601String(),
                'venueGatesOpenDate' => $this->venueGatesOpenDate?->toIso8601String(),
                'venueFanZoneOpenDate' => $this->venueFanZoneOpenDate?->toIso8601String(),
                'venueBoxOfficeOpenDate' => $this->venueBoxOfficeOpenDate?->toIso8601String(),
                'venueParkingLotsOpenDate' => $this->venueParkingLotsOpenDate?->toIso8601String(),
            ], fn ($value) => $value !== null),
        );
    }

    /**
     * Apple's `venueLocation` tag only carries coordinates, so the altitude and relevant text
     * a `Location` may hold for `addLocation()` are dropped here.
     *
     * @return array<string, float>|null
     */
    protected function compileVenueLocation(): ?array
    {
        if ($this->venueLocation === null) {
            return null;
        }

        return [
            'latitude' => $this->venueLocation->latitude,
            'longitude' => $this->venueLocation->longitude,
        ];
    }

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $this->preferredStyleSchemes = $this->data['preferredStyleSchemes'] ?? null;
    }

    protected function uncompileSemantics(): void
    {
        parent::uncompileSemantics();

        $semantics = $this->data['semantics'] ?? [];

        $this->venueName = $semantics['venueName'] ?? null;
        $this->venueLocation = empty($semantics['venueLocation'])
            ? null
            : Location::fromArray($semantics['venueLocation']);
        $this->venueEntrance = $semantics['venueEntrance'] ?? null;
        $this->venueEntranceDoor = $semantics['venueEntranceDoor'] ?? null;
        $this->venueEntranceGate = $semantics['venueEntranceGate'] ?? null;
        $this->venueEntrancePortal = $semantics['venueEntrancePortal'] ?? null;
        $this->venuePhoneNumber = $semantics['venuePhoneNumber'] ?? null;
        $this->venueRoom = $semantics['venueRoom'] ?? null;
        $this->venueRegionName = $semantics['venueRegionName'] ?? null;
        $this->venueOpenDate = $this->parseSemanticDate($semantics, 'venueOpenDate');
        $this->venueCloseDate = $this->parseSemanticDate($semantics, 'venueCloseDate');
        $this->venueDoorsOpenDate = $this->parseSemanticDate($semantics, 'venueDoorsOpenDate');
        $this->venueGatesOpenDate = $this->parseSemanticDate($semantics, 'venueGatesOpenDate');
        $this->venueFanZoneOpenDate = $this->parseSemanticDate($semantics, 'venueFanZoneOpenDate');
        $this->venueBoxOfficeOpenDate = $this->parseSemanticDate($semantics, 'venueBoxOfficeOpenDate');
        $this->venueParkingLotsOpenDate = $this->parseSemanticDate($semantics, 'venueParkingLotsOpenDate');
    }
}
