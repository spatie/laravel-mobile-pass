<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Concerns;

use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

trait HasSeats
{
    protected ?Collection $seats = null;

    /** An array of objects that represent the details for each seat at an event or on a transit journey. */
    public function setSeats(Seat ...$seat): static
    {
        $this->seats = collect($seat);

        return $this;
    }

    /** @param  array<string, mixed>  $semantics */
    protected function uncompileSeats(array $semantics): void
    {
        $this->seats = empty($semantics['seats'])
            ? null
            : collect($semantics['seats'])->map(fn (array $seat) => Seat::fromArray($seat));
    }
}
