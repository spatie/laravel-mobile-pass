<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;

/**
 * @phpstan-consistent-constructor
 */
abstract class GooglePassClass
{
    protected string $reviewStatus = 'UNDER_REVIEW';

    protected ?string $issuerName = null;

    protected ?string $backgroundColor = null;

    abstract protected static function resourceName(): string;

    abstract protected static function validator(): GooglePassClassValidator;

    /** @return array<string, mixed> */
    abstract protected function compileData(): array;

    /** @param array<string, mixed> $payload */
    abstract protected function applyHydratedPayload(array $payload): void;

    public function __construct(protected string $suffix) {}

    public static function make(string $suffix): static
    {
        return new static($suffix);
    }

    public function setIssuerName(string $issuerName): static
    {
        $this->issuerName = $issuerName;

        return $this;
    }

    public function setBackgroundColor(string $hex): static
    {
        $this->backgroundColor = $hex;

        return $this;
    }

    public function id(): string
    {
        return GoogleCredentials::issuerId().'.'.$this->suffix;
    }

    public function save(): static
    {
        $payload = static::validator()->validate($this->compileData() + ['id' => $this->id()]);

        app(GoogleWalletClient::class)->insertClass(static::resourceName(), $this->id(), $payload);

        return $this;
    }

    /**
     * Google has no hard delete for classes. Flipping reviewStatus to REJECTED
     * stops Google from promoting the class while existing passes keep working.
     */
    public function retire(): static
    {
        app(GoogleWalletClient::class)->patchClass(static::resourceName(), $this->id(), [
            'reviewStatus' => 'REJECTED',
        ]);

        return $this;
    }

    /** @return Collection<int, static> */
    public static function all(): Collection
    {
        $raw = app(GoogleWalletClient::class)->listClasses(static::resourceName());

        return collect($raw)->map(fn (array $payload) => static::hydrate($payload));
    }

    public static function find(string $suffix): ?static
    {
        $id = GoogleCredentials::issuerId().'.'.$suffix;

        try {
            $payload = app(GoogleWalletClient::class)->getClass(static::resourceName(), $id);
        } catch (GoogleWalletApiError $exception) {
            if ($exception->status === 404) {
                return null;
            }

            throw $exception;
        }

        return static::hydrate($payload);
    }

    /** @param array<string, mixed> $payload */
    protected static function hydrate(array $payload): static
    {
        $id = (string) ($payload['id'] ?? '');
        $suffix = Str::after($id, '.');

        $class = new static($suffix);
        $class->applyHydratedPayload($payload);

        return $class;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function filterEmpty(array $payload): array
    {
        return array_filter($payload, fn ($value) => $value !== null && $value !== []);
    }

    /** @param array<string, mixed> $payload */
    protected function hydrateCommonFields(array $payload): void
    {
        if (isset($payload['issuerName'])) {
            $this->issuerName = (string) $payload['issuerName'];
        }

        if (isset($payload['reviewStatus'])) {
            $this->reviewStatus = (string) $payload['reviewStatus'];
        }

        if (isset($payload['hexBackgroundColor'])) {
            $this->backgroundColor = (string) $payload['hexBackgroundColor'];
        }
    }
}
