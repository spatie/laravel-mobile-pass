<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasSeats;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Color;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\EventDateInfo;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\EventType;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;
    use HasSeats;

    protected PassType $type = PassType::EventTicket;

    /** @var array<int, string>|null */
    protected ?array $preferredStyleSchemes = null;

    protected ?Color $footerBackgroundColor = null;

    protected ?Color $stripColor = null;

    protected ?bool $suppressHeaderDarkening = null;

    protected ?bool $useAutomaticColors = null;

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

    protected ?string $eventName = null;

    protected ?EventType $eventType = null;

    protected ?Carbon $eventStartDate = null;

    protected ?EventDateInfo $eventStartDateInfo = null;

    protected ?Carbon $eventEndDate = null;

    protected ?string $admissionLevel = null;

    protected ?string $admissionLevelAbbreviation = null;

    protected ?string $attendeeName = null;

    protected ?string $additionalTicketAttributes = null;

    protected ?string $entranceDescription = null;

    protected ?string $genre = null;

    protected ?bool $tailgatingAllowed = null;

    protected ?int $durationInSeconds = null;

    protected ?bool $silenceRequested = null;

    /** @var array<int, string>|null */
    protected ?array $performerNames = null;

    /** @var array<int, string>|null */
    protected ?array $artistIds = null;

    /** @var array<int, string>|null */
    protected ?array $albumIds = null;

    /** @var array<int, string>|null */
    protected ?array $playlistIds = null;

    protected ?string $awayTeamAbbreviation = null;

    protected ?string $awayTeamName = null;

    protected ?string $awayTeamLocation = null;

    protected ?string $homeTeamAbbreviation = null;

    protected ?string $homeTeamName = null;

    protected ?string $homeTeamLocation = null;

    protected ?string $leagueAbbreviation = null;

    protected ?string $leagueName = null;

    protected ?string $sportName = null;

    protected static function validator(): ApplePassValidator
    {
        return new EventTicketApplePassValidator;
    }

    public function usePosterLayout(): static
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

        return $this;
    }

    public function setFooterBackgroundColor(string $hex): static
    {
        $this->footerBackgroundColor = Color::makeFromHex($hex);

        return $this;
    }

    public function setStripColor(string $hex): static
    {
        $this->stripColor = Color::makeFromHex($hex);

        return $this;
    }

    public function setSuppressHeaderDarkening(bool $suppressHeaderDarkening): static
    {
        $this->suppressHeaderDarkening = $suppressHeaderDarkening;

        return $this;
    }

    public function setUseAutomaticColors(bool $useAutomaticColors): static
    {
        $this->useAutomaticColors = $useAutomaticColors;

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

    /** The full name of the event, such as the title of a movie. */
    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    /** The type of event. */
    public function setEventType(EventType $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    /** The date and time the event starts. */
    public function setEventStartDate(Carbon $eventStartDate): static
    {
        $this->eventStartDate = $eventStartDate;

        return $this;
    }

    /** An object that provides information for the date and time the event starts. */
    public function setEventStartDateInfo(EventDateInfo $eventStartDateInfo): static
    {
        $this->eventStartDateInfo = $eventStartDateInfo;

        return $this;
    }

    /** The date and time the event ends. */
    public function setEventEndDate(Carbon $eventEndDate): static
    {
        $this->eventEndDate = $eventEndDate;

        return $this;
    }

    /** The event's admission level. */
    public function setAdmissionLevel(string $admissionLevel): static
    {
        $this->admissionLevel = $admissionLevel;

        return $this;
    }

    /** An abbreviation for the event's admission level. */
    public function setAdmissionLevelAbbreviation(string $admissionLevelAbbreviation): static
    {
        $this->admissionLevelAbbreviation = $admissionLevelAbbreviation;

        return $this;
    }

    /** The name of the ticket's attendee. */
    public function setAttendeeName(string $attendeeName): static
    {
        $this->attendeeName = $attendeeName;

        return $this;
    }

    /** Additional ticket attributes to display, such as the ticket's original price. */
    public function setAdditionalTicketAttributes(string $additionalTicketAttributes): static
    {
        $this->additionalTicketAttributes = $additionalTicketAttributes;

        return $this;
    }

    /** A brief description of the entrance to use for the event, such as "Gate A". */
    public function setEntranceDescription(string $entranceDescription): static
    {
        $this->entranceDescription = $entranceDescription;

        return $this;
    }

    /** The genre of the performance. */
    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    /** A Boolean value that indicates whether tailgating is allowed at the event. */
    public function setTailgatingAllowed(bool $tailgatingAllowed): static
    {
        $this->tailgatingAllowed = $tailgatingAllowed;

        return $this;
    }

    /** The duration of the event, in seconds. */
    public function setDuration(int $durationInSeconds): static
    {
        $this->durationInSeconds = $durationInSeconds;

        return $this;
    }

    /** A Boolean value that indicates whether silence is requested during the event. */
    public function setSilenceRequested(bool $silenceRequested): static
    {
        $this->silenceRequested = $silenceRequested;

        return $this;
    }

    /** An array of the full names of the performers and opening acts at the event, in decreasing order of significance. */
    public function setPerformerNames(string ...$performerNames): static
    {
        $this->performerNames = $performerNames;

        return $this;
    }

    /** An array of the Apple Music persistent ID for each artist performing at the event, in decreasing order of significance. */
    public function setArtistIds(string ...$artistIds): static
    {
        $this->artistIds = $artistIds;

        return $this;
    }

    /** An array of the Apple Music persistent ID for each album associated with the event. */
    public function setAlbumIds(string ...$albumIds): static
    {
        $this->albumIds = $albumIds;

        return $this;
    }

    /** An array of the Apple Music persistent ID for each playlist associated with the event. */
    public function setPlaylistIds(string ...$playlistIds): static
    {
        $this->playlistIds = $playlistIds;

        return $this;
    }

    /** The unique abbreviation of the away team's name. */
    public function setAwayTeamAbbreviation(string $awayTeamAbbreviation): static
    {
        $this->awayTeamAbbreviation = $awayTeamAbbreviation;

        return $this;
    }

    /** The name of the away team. */
    public function setAwayTeamName(string $awayTeamName): static
    {
        $this->awayTeamName = $awayTeamName;

        return $this;
    }

    /** The location of the away team. */
    public function setAwayTeamLocation(string $awayTeamLocation): static
    {
        $this->awayTeamLocation = $awayTeamLocation;

        return $this;
    }

    /** The unique abbreviation of the home team's name. */
    public function setHomeTeamAbbreviation(string $homeTeamAbbreviation): static
    {
        $this->homeTeamAbbreviation = $homeTeamAbbreviation;

        return $this;
    }

    /** The name of the home team. */
    public function setHomeTeamName(string $homeTeamName): static
    {
        $this->homeTeamName = $homeTeamName;

        return $this;
    }

    /** The location of the home team. */
    public function setHomeTeamLocation(string $homeTeamLocation): static
    {
        $this->homeTeamLocation = $homeTeamLocation;

        return $this;
    }

    /** The abbreviation of the sports league that the event belongs to. */
    public function setLeagueAbbreviation(string $leagueAbbreviation): static
    {
        $this->leagueAbbreviation = $leagueAbbreviation;

        return $this;
    }

    /** The name of the sports league that the event belongs to. */
    public function setLeagueName(string $leagueName): static
    {
        $this->leagueName = $leagueName;

        return $this;
    }

    /** The name of the sport that the event relates to. */
    public function setSportName(string $sportName): static
    {
        $this->sportName = $sportName;

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
                'footerBackgroundColor' => $this->footerBackgroundColor ? (string) $this->footerBackgroundColor : null,
                'stripColor' => $this->stripColor ? (string) $this->stripColor : null,
                'suppressHeaderDarkening' => $this->suppressHeaderDarkening,
                'useAutomaticColors' => $this->useAutomaticColors,
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
                'eventName' => $this->eventName,
                'eventType' => $this->eventType?->value,
                'eventStartDate' => $this->eventStartDate?->toIso8601String(),
                'eventStartDateInfo' => $this->eventStartDateInfo?->toArray(),
                'eventEndDate' => $this->eventEndDate?->toIso8601String(),
                'admissionLevel' => $this->admissionLevel,
                'admissionLevelAbbreviation' => $this->admissionLevelAbbreviation,
                'attendeeName' => $this->attendeeName,
                'additionalTicketAttributes' => $this->additionalTicketAttributes,
                'entranceDescription' => $this->entranceDescription,
                'genre' => $this->genre,
                'tailgatingAllowed' => $this->tailgatingAllowed,
                'duration' => $this->durationInSeconds,
                'silenceRequested' => $this->silenceRequested,
                'performerNames' => $this->performerNames,
                'artistIDs' => $this->artistIds,
                'albumIDs' => $this->albumIds,
                'playlistIDs' => $this->playlistIds,
                'awayTeamAbbreviation' => $this->awayTeamAbbreviation,
                'awayTeamName' => $this->awayTeamName,
                'awayTeamLocation' => $this->awayTeamLocation,
                'homeTeamAbbreviation' => $this->homeTeamAbbreviation,
                'homeTeamName' => $this->homeTeamName,
                'homeTeamLocation' => $this->homeTeamLocation,
                'leagueAbbreviation' => $this->leagueAbbreviation,
                'leagueName' => $this->leagueName,
                'sportName' => $this->sportName,
                'seats' => $this->seats?->toArray(),
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
        $this->footerBackgroundColor = Color::makeFromRgbString($this->data['footerBackgroundColor'] ?? null);
        $this->stripColor = Color::makeFromRgbString($this->data['stripColor'] ?? null);
        $this->suppressHeaderDarkening = $this->data['suppressHeaderDarkening'] ?? null;
        $this->useAutomaticColors = $this->data['useAutomaticColors'] ?? null;
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
        $this->eventName = $semantics['eventName'] ?? null;
        $this->eventType = ! empty($semantics['eventType']) ? EventType::tryFrom($semantics['eventType']) : null;
        $this->eventStartDate = $this->parseSemanticDate($semantics, 'eventStartDate');
        $this->eventStartDateInfo = empty($semantics['eventStartDateInfo'])
            ? null
            : EventDateInfo::fromArray($semantics['eventStartDateInfo']);
        $this->eventEndDate = $this->parseSemanticDate($semantics, 'eventEndDate');
        $this->admissionLevel = $semantics['admissionLevel'] ?? null;
        $this->admissionLevelAbbreviation = $semantics['admissionLevelAbbreviation'] ?? null;
        $this->attendeeName = $semantics['attendeeName'] ?? null;
        $this->additionalTicketAttributes = $semantics['additionalTicketAttributes'] ?? null;
        $this->entranceDescription = $semantics['entranceDescription'] ?? null;
        $this->genre = $semantics['genre'] ?? null;
        $this->tailgatingAllowed = $semantics['tailgatingAllowed'] ?? null;
        $this->durationInSeconds = $semantics['duration'] ?? null;
        $this->silenceRequested = $semantics['silenceRequested'] ?? null;
        $this->performerNames = $semantics['performerNames'] ?? null;
        $this->artistIds = $semantics['artistIDs'] ?? null;
        $this->albumIds = $semantics['albumIDs'] ?? null;
        $this->playlistIds = $semantics['playlistIDs'] ?? null;
        $this->awayTeamAbbreviation = $semantics['awayTeamAbbreviation'] ?? null;
        $this->awayTeamName = $semantics['awayTeamName'] ?? null;
        $this->awayTeamLocation = $semantics['awayTeamLocation'] ?? null;
        $this->homeTeamAbbreviation = $semantics['homeTeamAbbreviation'] ?? null;
        $this->homeTeamName = $semantics['homeTeamName'] ?? null;
        $this->homeTeamLocation = $semantics['homeTeamLocation'] ?? null;
        $this->leagueAbbreviation = $semantics['leagueAbbreviation'] ?? null;
        $this->leagueName = $semantics['leagueName'] ?? null;
        $this->sportName = $semantics['sportName'] ?? null;
        $this->uncompileSeats($semantics);
    }
}
