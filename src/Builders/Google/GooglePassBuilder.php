<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelMobilePass\Actions\Google\CreateGoogleObjectAction;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\ImageModule;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Link;
use Spatie\LaravelMobilePass\Builders\Google\Entities\TextModule;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Support\WifiUri;

/**
 * @phpstan-consistent-constructor
 */
abstract class GooglePassBuilder
{
    protected ?string $classSuffix = null;

    protected ?string $objectSuffix = null;

    protected ?Barcode $barcode = null;

    protected string $state = 'ACTIVE';

    protected PassType $type;

    /** @var array<int, Link> */
    protected array $links = [];

    /** @var array<int, TextModule> */
    protected array $textModules = [];

    /** @var array<int, ImageModule> */
    protected array $imageModules = [];

    abstract protected static function validator(): GooglePassObjectValidator;

    abstract protected static function classResource(): string;

    abstract protected static function objectResource(): string;

    /** @return array<string, mixed> */
    abstract protected function compileData(): array;

    public static function make(): static
    {
        return new static;
    }

    public static function name(): string
    {
        return Str::snake(Str::replaceLast('PassBuilder', '', class_basename(static::class)));
    }

    public function platform(): Platform
    {
        return Platform::Google;
    }

    public function setClass(string $suffix): static
    {
        $this->classSuffix = $suffix;

        return $this;
    }

    public function setObjectSuffix(string $suffix): static
    {
        $this->objectSuffix = $suffix;

        return $this;
    }

    public function setBarcode(BarcodeType $format, string $message, ?string $altText = null): static
    {
        $barcode = Barcode::make($format, $message);

        if ($altText !== null) {
            $barcode->withAltText($altText);
        }

        $this->barcode = $barcode;

        return $this;
    }

    public function setWifiBarcode(
        string $ssid,
        ?string $password = null,
        bool $hidden = false,
        ?string $altText = null,
    ): static {
        return $this->setBarcode(
            BarcodeType::Qr,
            WifiUri::build($ssid, $password, $hidden),
            $altText ?? $ssid,
        );
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

    public function objectId(): string
    {
        $this->objectSuffix ??= (string) Str::uuid();

        return GoogleCredentials::issuerId().'.'.$this->objectSuffix;
    }

    public function classId(): string
    {
        if ($this->classSuffix === null) {
            throw new RuntimeException('Call setClass() before saving a Google pass.');
        }

        return GoogleCredentials::issuerId().'.'.$this->classSuffix;
    }

    public function save(): MobilePass
    {
        $payload = $this->compileGoogleObjectPayload();

        static::validator()->validate($payload);

        app(CreateGoogleObjectAction::class)->execute(
            static::objectResource(),
            $this->objectId(),
            $payload,
        );

        $mobilePassClass = Config::mobilePassModel();

        return $mobilePassClass::query()->create([
            'pass_serial' => $this->objectId(),
            'type' => $this->type->value,
            'platform' => Platform::Google,
            'builder_name' => static::name(),
            'content' => [
                'googleClassType' => static::classResource(),
                'googleObjectId' => $this->objectId(),
                'googleClassId' => $this->classId(),
                'googleObjectPayload' => $payload,
            ],
            'images' => [],
        ]);
    }

    /** @return array<string, mixed> */
    protected function compileGoogleObjectPayload(): array
    {
        return $this->filterEmpty(array_merge([
            'id' => $this->objectId(),
            'classId' => $this->classId(),
            'state' => $this->state,
            'barcode' => $this->compileBarcode(),
        ], $this->compileData(), $this->compileModules()));
    }

    /** @return array<string, mixed> */
    protected function compileModules(): array
    {
        return $this->filterEmpty([
            'linksModuleData' => $this->compileLinks(),
            'textModulesData' => array_map(fn (TextModule $module) => $module->toArray(), $this->textModules),
            'imageModulesData' => array_map(fn (ImageModule $module) => $module->toArray(), $this->imageModules),
        ]);
    }

    /** @return array<string, mixed> */
    protected function compileLinks(): array
    {
        if ($this->links === []) {
            return [];
        }

        return ['uris' => array_map(fn (Link $link) => $link->toArray(), $this->links)];
    }

    /** @return array<string, mixed>|null */
    protected function compileBarcode(): ?array
    {
        if ($this->barcode === null) {
            return null;
        }

        return $this->filterEmpty([
            'type' => $this->translateBarcodeType($this->barcode->format),
            'value' => $this->barcode->message,
            'alternateText' => $this->barcode->altText,
        ]);
    }

    protected function translateBarcodeType(BarcodeType $type): string
    {
        return match ($type) {
            BarcodeType::Qr => 'QR_CODE',
            BarcodeType::Pdf417 => 'PDF_417',
            BarcodeType::Aztec => 'AZTEC',
            BarcodeType::Code128 => 'CODE_128',
        };
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function filterEmpty(array $values): array
    {
        return array_filter($values, fn ($value) => $value !== null && $value !== []);
    }
}
