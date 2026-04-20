<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;

class LocalizedString implements Arrayable
{
    /** @var array<int, array{language: string, value: string}> */
    protected array $translations = [];

    public function __construct(
        public string $defaultValue,
        public string $defaultLanguage = 'en-US',
    ) {}

    public static function of(string $defaultValue, string $defaultLanguage = 'en-US'): self
    {
        return new self($defaultValue, $defaultLanguage);
    }

    public function addTranslation(string $language, string $value): self
    {
        $this->translations[] = ['language' => $language, 'value' => $value];

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = [
            'defaultValue' => [
                'language' => $this->defaultLanguage,
                'value' => $this->defaultValue,
            ],
        ];

        if ($this->translations !== []) {
            $payload['translatedValues'] = $this->translations;
        }

        return $payload;
    }
}
