<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class PersonName implements Arrayable
{
    public function __construct(
        public ?string $familyName = null,
        public ?string $givenName = null,
        public ?string $middleName = null,
        public ?string $namePrefix = null,
        public ?string $nameSuffix = null,
        public ?string $nickname = null,
        public ?string $phoneticRepresentation = null
    ) {}

    public static function make(
        ?string $familyName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $namePrefix = null,
        ?string $nameSuffix = null,
        ?string $nickname = null,
        ?string $phoneticRepresentation = null
    ): static {
        return new self(
            familyName: $familyName,
            givenName: $givenName,
            middleName: $middleName,
            namePrefix: $namePrefix,
            nameSuffix: $nameSuffix,
            nickname: $nickname,
            phoneticRepresentation: $phoneticRepresentation,
        );
    }

    public static function fromArray(array $values): static
    {
        return new self(
            familyName: $values['familyName'] ?? null,
            givenName: $values['givenName'] ?? null,
            middleName: $values['middleName'] ?? null,
            namePrefix: $values['namePrefix'] ?? null,
            nameSuffix: $values['nameSuffix'] ?? null,
            nickname: $values['nickname'] ?? null,
            phoneticRepresentation: $values['phoneticRepresentation'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'familyName' => $this->familyName,
            'givenName' => $this->givenName,
            'middleName' => $this->middleName,
            'namePrefix' => $this->namePrefix,
            'nickname' => $this->nickname,
            'phoneticRepresentation' => $this->phoneticRepresentation,
        ]);
    }
}
