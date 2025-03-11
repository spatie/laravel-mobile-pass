<?php

namespace Spatie\LaravelMobilePass\Entities;

class PersonName
{
    public function __construct(
        public ?string $familyName = null,
        public ?string $givenName = null,
        public ?string $middleName = null,
        public ?string $namePrefix = null,
        public ?string $nameSuffix = null,
        public ?string $nickname = null,
        public ?string $phoneticRepresentation = null
    )
    {
    }

    public static function make(
        ?string $familyName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $namePrefix = null,
        ?string $nameSuffix = null,
        ?string $nickname = null,
        ?string $phoneticRepresentation = null
    )
    {
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
}