<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Colour;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\NfcPayload;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Price;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\WifiNetwork;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\DateType;
use Spatie\LaravelMobilePass\Enums\FieldType;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Enums\TimeStyleType;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\MobilePass;

/**
 * @phpstan-consistent-constructor
 */
abstract class ApplePassBuilder
{
    protected ?array $data = null;

    protected PassType $type;

    protected ?string $serialNumber = null;

    protected ?string $organisationName = null;

    protected ?string $passTypeIdentifier = null;

    protected ?string $authenticationToken = null;

    protected ?string $teamIdentifier = null;

    protected ?Colour $backgroundColour = null;

    protected ?Colour $foregroundColour = null;

    protected ?Colour $labelColour = null;

    protected ?string $description = null;

    protected ?Price $totalPrice = null;

    protected ?Collection $wifiDetails = null;

    protected ?Collection $primaryFields = null;

    protected ?Collection $secondaryFields = null;

    protected ?Collection $auxiliaryFields = null;

    protected ?Collection $headerFields = null;

    protected ?Collection $backFields = null;

    protected array $images = [];

    protected ?string $downloadName = null;

    protected ?Barcode $barcode = null;

    protected ?Carbon $relevantDate = null;

    protected ?int $maxDistance = null;

    /** @var array<int, Location> */
    protected array $locations = [];

    protected ?NfcPayload $nfc = null;

    abstract protected static function validator(): ApplePassValidator;

    public static function make(): static
    {
        return new static;
    }

    /** @internal */
    public static function hydrate(MobilePass $model): static
    {
        return new static($model->content, $model->images, $model);
    }

    public static function name(): string
    {
        return Str::snake(
            Str::replaceLast('PassBuilder', '', class_basename(static::class))
        );
    }

    public function platform(): Platform
    {
        return Platform::Apple;
    }

    public function __construct(array $data = [], array $images = [], protected ?MobilePass $model = null)
    {
        $this->data = $data;
        $this->images = $images;
        $this->downloadName = $model?->download_name;

        $this->uncompileContent();
    }

    protected static function appleConfig(string $key): mixed
    {
        return config("mobile-pass.apple.{$key}");
    }

    public function setDownloadName(string $downloadName): self
    {
        $this->downloadName = $downloadName;

        return $this;
    }

    public function setLogoImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->images['logo'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setIconImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->images['icon'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function addHeaderField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addField($key, $value, FieldType::Header, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addSecondaryField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addField($key, $value, FieldType::Secondary, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addAuxiliaryField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addField($key, $value, FieldType::Auxiliary, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addBackField(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        return $this->addField($key, $value, FieldType::Back, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    public function addField(
        string $key,
        string $value,
        FieldType $type = FieldType::Primary,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): self {
        $field = FieldContent::make($key)
            ->withValue($value)
            ->withLabel($label ?? Str::headline($key));

        if ($changeMessage !== null) {
            $field->showMessageWhenChanged($changeMessage);
        }

        if ($dateStyle !== null) {
            $field->usingDateType($dateStyle);
        }

        if ($timeStyle !== null) {
            $field->usingTimeType($timeStyle);
        }

        if ($showDateAsRelative === true) {
            $field->showDateAsRelative();
        }

        $property = $type->value;

        $this->{$property} ??= collect();
        $this->{$property}[$key] = $field;

        return $this;
    }

    public function updateField(
        string $key,
        string $value,
        ?string $changeMessage = null,
        ?string $label = null,
    ): self {
        foreach (FieldType::cases() as $type) {
            $property = $type->value;

            if ($this->{$property} === null) {
                continue;
            }

            $this->{$property} = $this->{$property}->map(function (FieldContent $field) use ($key, $value, $changeMessage, $label) {
                if ($field->key !== $key) {
                    return $field;
                }

                $field->withValue($value);

                if ($changeMessage !== null) {
                    $field->showMessageWhenChanged($changeMessage);
                }

                if ($label !== null) {
                    $field->withLabel($label);
                }

                return $field;
            });
        }

        return $this;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function setOrganisationName(string $organisationName): self
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setBackgroundColour(string $hex): self
    {
        $this->backgroundColour = Colour::makeFromHex($hex);

        return $this;
    }

    public function setForegroundColour(string $hex): self
    {
        $this->foregroundColour = Colour::makeFromHex($hex);

        return $this;
    }

    public function setLabelColour(string $hex): self
    {
        $this->labelColour = Colour::makeFromHex($hex);

        return $this;
    }

    /**
     * The total price for the pass.
     */
    public function setTotalPrice(Price $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function setWifiDetails(WifiNetwork ...$wifiNetwork): self
    {
        $this->wifiDetails = collect($wifiNetwork);

        return $this;
    }

    public function setBarcode(BarcodeType $format, string $message, ?string $altText = null): self
    {
        $barcode = Barcode::make($format, $message);

        if ($altText !== null) {
            $barcode->withAltText($altText);
        }

        $this->barcode = $barcode;

        return $this;
    }

    public function setRelevantDate(Carbon $date): self
    {
        $this->relevantDate = $date;

        return $this;
    }

    public function addLocation(
        float $latitude,
        float $longitude,
        ?float $altitude = null,
        ?string $relevantText = null,
    ): self {
        $this->locations[] = new Location($latitude, $longitude, $altitude, $relevantText);

        return $this;
    }

    public function setMaxDistance(int $meters): self
    {
        $this->maxDistance = $meters;

        return $this;
    }

    public function setNfc(
        string $message,
        string $encryptionPublicKey,
        bool $requiresAuthentication = false,
    ): self {
        $this->nfc = new NfcPayload($message, $encryptionPublicKey, $requiresAuthentication);

        return $this;
    }

    protected function addImagesToFile(PKPass $pkPass): PKPass
    {
        foreach ($this->images as $filename => $image) {
            if (! $image instanceof Image) {
                $image = Image::make($image['x1Path'], $image['x2Path'], $image['x3Path']);
            }

            if ($image->x1Path) {
                $pkPass->addFile($image->x1Path, "{$filename}.png");
            }

            if ($image->x2Path) {
                $pkPass->addFile($image->x2Path, "{$filename}@2x.png");
            }

            if ($image->x3Path) {
                $pkPass->addFile($image->x3Path, "{$filename}@3x.png");
            }
        }

        return $pkPass;
    }

    public static function getCertificatePath(): string
    {
        $contents = self::appleConfig('certificate');

        if (empty($contents)) {
            return self::appleConfig('certificate_path');
        }

        $path = sys_get_temp_dir().'/LaravelMobilePass.p12';

        if (! file_exists($path)) {
            file_put_contents($path, base64_decode($contents));
        }

        return $path;
    }

    public static function getCertificatePassword(): string
    {
        return self::appleConfig('certificate_password');
    }

    public function save(): MobilePass
    {
        if ($this->model) {
            $this->model->update([
                'content' => $this->data(),
                'images' => $this->images,
                'download_name' => $this->downloadName,
            ]);

            return $this->model;
        }

        return MobilePass::query()->create([
            'type' => $this->type->value,
            'platform' => static::platform(),
            'builder_name' => static::name(),
            'content' => $this->data(),
            'images' => $this->images,
            'download_name' => $this->downloadName,
        ]);
    }

    public function data(): array
    {
        $configuredOrganisationName = self::appleConfig('organisation_name');

        if (empty($this->organisationName) && ! empty($configuredOrganisationName)) {
            $this->setOrganisationName($configuredOrganisationName);
        }

        if (empty($this->serialNumber)) {
            $this->serialNumber = (string) Str::uuid();
        }

        $compiledData = array_filter(
            $this->compileData(),
            fn ($value) => ! empty($value)
        );

        return $this->validator()->validate($compiledData);
    }

    public function generate(): string
    {
        $pkPass = new PKPass(
            self::getCertificatePath(),
            self::getCertificatePassword(),
        );

        $pkPass->setData($this->data());

        $this->addImagesToFile($pkPass);

        return $pkPass->create(output: false);
    }

    protected function compileSemantics(): ?array
    {
        return array_filter([
            'totalPrice' => $this->totalPrice?->toArray(),
            'wifiAccess' => $this->wifiDetails?->toArray(),
        ]);
    }

    protected function compileData(): array
    {
        $barcode = $this->barcode?->toArray();

        return array_merge($this->data ?? [], array_filter([
            'formatVersion' => 1,
            'organizationName' => $this->organisationName,
            'passTypeIdentifier' => self::appleConfig('type_identifier'),
            'serialNumber' => $this->serialNumber,
            'authenticationToken' => self::appleConfig('webservice.secret'),
            'webServiceURL' => $this->webServiceURL(),
            'teamIdentifier' => self::appleConfig('team_identifier'),
            'description' => $this->description,
            'semantics' => $this->compileSemantics(),
            'backgroundColor' => (string) $this->backgroundColour,
            'foregroundColor' => (string) $this->foregroundColour,
            'labelColor' => (string) $this->labelColour,
            'barcode' => $barcode,
            'barcodes' => $barcode ? [$barcode] : null,
            'relevantDate' => $this->relevantDate?->toIso8601String(),
            'locations' => empty($this->locations) ? null : array_map(
                fn (Location $location) => $location->toArray(),
                $this->locations,
            ),
            'maxDistance' => $this->maxDistance,
            'nfc' => $this->nfc?->toArray(),
            'userInfo' => [
                'passType' => $this->type->value,
            ],
        ]));
    }

    protected function webServiceURL(): ?string
    {
        $host = self::appleConfig('webservice.host');

        if ($host !== null && $host !== '' && ! str_starts_with($host, 'https://')) {
            throw InvalidConfig::webserviceHostMustBeHttps($host);
        }

        if (empty($host)) {
            $appUrl = (string) config('app.url');

            $host = str_starts_with($appUrl, 'https://') ? $appUrl : null;
        }

        if (! $host) {
            return null;
        }

        return rtrim($host, '/').'/passkit';
    }

    protected function uncompileSemantics(): void
    {
        $semantics = $this->data['semantics'] ?? [];

        $this->totalPrice = empty($semantics['totalPrice'])
            ? null
            : Price::fromArray($semantics['totalPrice']);

        $this->wifiDetails = empty($semantics['wifiAccess'])
            ? null
            : collect($semantics['wifiAccess'])->map(fn (array $wifi) => WifiNetwork::fromArray($wifi));
    }

    protected function uncompileContent(): void
    {
        $this->organisationName = $this->data['organizationName'] ?? null;
        $this->passTypeIdentifier = $this->data['passTypeIdentifier'] ?? null;
        $this->authenticationToken = $this->data['authenticationToken'] ?? null;
        $this->teamIdentifier = $this->data['teamIdentifier'] ?? null;
        $this->description = $this->data['description'] ?? null;
        $this->backgroundColour = Colour::makeFromRgbString($this->data['backgroundColor'] ?? null);
        $this->foregroundColour = Colour::makeFromRgbString($this->data['foregroundColor'] ?? null);
        $this->labelColour = Colour::makeFromRgbString($this->data['labelColor'] ?? null);

        $this->barcode = empty($this->data['barcode'])
            ? null
            : Barcode::fromArray($this->data['barcode']);

        $this->relevantDate = empty($this->data['relevantDate'])
            ? null
            : Carbon::parse($this->data['relevantDate']);

        $this->locations = array_map(
            fn (array $location) => Location::fromArray($location),
            $this->data['locations'] ?? [],
        );

        $this->maxDistance = $this->data['maxDistance'] ?? null;

        $this->nfc = empty($this->data['nfc'])
            ? null
            : NfcPayload::fromArray($this->data['nfc']);

        $this->uncompileSemantics();

        $this->uncompileFieldSet('headerFields');
        $this->uncompileFieldSet('primaryFields');
        $this->uncompileFieldSet('secondaryFields');
        $this->uncompileFieldSet('auxiliaryFields');
        $this->uncompileFieldSet('backFields');
    }

    protected function uncompileFieldSet(string $fieldSetName): void
    {
        $this->{$fieldSetName} = collect();

        foreach ($this->data[$this->type->value][$fieldSetName] ?? [] as $field) {
            $this->{$fieldSetName}[$field['key']] = FieldContent::fromArray($field);
        }
    }
}
