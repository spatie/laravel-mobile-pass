<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\EventTicketObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends GooglePassBuilder
{
    protected PassType $type = PassType::EventTicket;

    protected ?string $attendeeName = null;

    protected ?LocalizedString $section = null;

    protected ?LocalizedString $row = null;

    protected ?LocalizedString $seat = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new EventTicketObjectValidator;
    }

    protected static function classResource(): string
    {
        return 'eventTicketClass';
    }

    protected static function objectResource(): string
    {
        return 'eventTicketObject';
    }

    public function setAttendeeName(string $name): self
    {
        $this->attendeeName = $name;

        return $this;
    }

    public function setSection(string $section, string $language = 'en-US'): self
    {
        $this->section = LocalizedString::of($section, $language);
        
        return $this;
    }


    public function setRow(string $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function setSeat(string $seat): self
    {
        $this->seat = $seat;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $seatInfo = $this->filterEmpty([
            'section' => $this->section?->toArray(),
            'row' => $this->row?->toArray(),
            'seat' => $this->seat?->toArray(),
        ]);


        return $this->filterEmpty([
            'ticketHolderName' => $this->attendeeName,
            'seatInfo' => $seatInfo,
        ]);
    }
}
