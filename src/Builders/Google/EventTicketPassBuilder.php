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

    public function setSection(LocalizedString|string $section, string $language = 'en-US'): self
    {
        if ($section instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->section = $section instanceof LocalizedString
            ? $section
            : LocalizedString::of($section, $language);

        return $this;
    }

    public function setRow(LocalizedString|string $row, string $language = 'en-US'): self
    {
        if ($row instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->row = $row instanceof LocalizedString
            ? $row
            : LocalizedString::of($row, $language);

        return $this;
    }

    public function setSeat(LocalizedString|string $seat, string $language = 'en-US'): self
    {
        if ($seat instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->seat = $seat instanceof LocalizedString
            ? $seat
            : LocalizedString::of($seat, $language);

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
