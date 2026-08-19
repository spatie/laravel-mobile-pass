<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasArtworkImage;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\EventTicketApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends ApplePassBuilder
{
    use HasArtworkImage;

    protected PassType $type = PassType::EventTicket;

    protected ?array $preferredStyleSchemes = null;

    protected static function validator(): ApplePassValidator
    {
        return new EventTicketApplePassValidator;
    }

    public function usePosterLayout(): self
    {
        $this->preferredStyleSchemes = ['posterEventTicket', 'eventTicket'];

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

    protected function uncompileContent(): void
    {
        parent::uncompileContent();

        $this->preferredStyleSchemes = $this->data['preferredStyleSchemes'] ?? null;
    }
}
