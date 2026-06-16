<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\ImageModule;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Link;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Google\Entities\TextModule;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletRequestFailed;
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

    /** @var array<int, Location> */
    protected array $locations = [];

    /** @var array<int, Link> */
    protected array $links = [];

    /** @var array<int, TextModule> */
    protected array $textModules = [];

    /** @var array<int, ImageModule> */
    protected array $imageModules = [];

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

    public function addLocation(float $latitude, float $longitude): static
    {
        $this->locations[] = new Location($latitude, $longitude);

        return $this;
    }

    public function addLink(string $uri, ?string $description = null): static
    {
        $this->links[] = new Link($uri, $description);

        return $this;
    }

    public function addTextModule(string $header, string $body, ?string $id = null): static
    {
        $this->textModules[] = new TextModule($header, $body, $id);

        return $this;
    }

    public function addImageModule(string $imageUrl, ?string $id = null): static
    {
        $this->imageModules[] = new ImageModule(Image::fromUrl($imageUrl), $id);

        return $this;
    }

    /** @return array<int, Location> */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /** @return array<int, Link> */
    public function getLinks(): array
    {
        return $this->links;
    }

    /** @return array<int, TextModule> */
    public function getTextModules(): array
    {
        return $this->textModules;
    }

    /** @return array<int, ImageModule> */
    public function getImageModules(): array
    {
        return $this->imageModules;
    }

    public function id(): string
    {
        return GoogleCredentials::issuerId().'.'.$this->suffix;
    }

    public function save(): static
    {
        $payload = static::validator()->validate(
            $this->compileData() + $this->compileModules() + ['id' => $this->id()]
        );

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
        } catch (GoogleWalletRequestFailed $exception) {
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

    /** @return array<string, mixed> */
    protected function compileModules(): array
    {
        return $this->filterEmpty([
            'locations' => array_map(fn (Location $location) => $location->toArray(), $this->locations),
            'linksModuleData' => $this->links === []
                ? []
                : ['uris' => array_map(fn (Link $link) => $link->toArray(), $this->links)],
            'textModulesData' => array_map(fn (TextModule $module) => $module->toArray(), $this->textModules),
            'imageModulesData' => array_map(fn (ImageModule $module) => $module->toArray(), $this->imageModules),
        ]);
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

        $this->locations = $this->hydrateLocations($payload);
        $this->links = $this->hydrateLinks($payload);
        $this->textModules = $this->hydrateTextModules($payload);
        $this->imageModules = $this->hydrateImageModules($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, Location>
     */
    protected function hydrateLocations(array $payload): array
    {
        return array_map(
            fn (array $location) => new Location(
                (float) ($location['latitude'] ?? 0),
                (float) ($location['longitude'] ?? 0),
            ),
            $payload['locations'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, Link>
     */
    protected function hydrateLinks(array $payload): array
    {
        return array_map(
            fn (array $link) => new Link(
                (string) ($link['uri'] ?? ''),
                isset($link['description']) ? (string) $link['description'] : null,
            ),
            $payload['linksModuleData']['uris'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, TextModule>
     */
    protected function hydrateTextModules(array $payload): array
    {
        return array_map(
            fn (array $module) => new TextModule(
                (string) ($module['header'] ?? ''),
                (string) ($module['body'] ?? ''),
                isset($module['id']) ? (string) $module['id'] : null,
            ),
            $payload['textModulesData'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, ImageModule>
     */
    protected function hydrateImageModules(array $payload): array
    {
        return array_map(
            fn (array $module) => new ImageModule(
                Image::fromUrl((string) ($module['mainImage']['sourceUri']['uri'] ?? '')),
                isset($module['id']) ? (string) $module['id'] : null,
            ),
            $payload['imageModulesData'] ?? [],
        );
    }

    /** @param  array<string, mixed>  $payload */
    protected function hydrateImage(array $payload, string $key): ?Image
    {
        $uri = $payload[$key]['sourceUri']['uri'] ?? null;

        if ($uri === null) {
            return null;
        }

        return Image::fromUrl((string) $uri);
    }

    /** @param array<string, mixed> $payload */
    protected function hydrateLocalizedString(array $payload, string $key): ?LocalizedString
    {
        $value = $payload[$key]['defaultValue']['value'] ?? null;

        if ($value === null) {
            return null;
        }

        $language = $payload[$key]['defaultValue']['language'] ?? 'en-US';

        return LocalizedString::of((string) $value, (string) $language);
    }
}
