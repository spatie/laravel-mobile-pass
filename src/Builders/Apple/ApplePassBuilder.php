<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PKPass\PKPass;
use PKPass\PKPassException;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Beacon;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Color;
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
use Spatie\LaravelMobilePass\Exceptions\InvalidCertificate;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\WifiUri;

/**
 * @phpstan-consistent-constructor
 */
abstract class ApplePassBuilder
{
    protected PassType $type;

    protected ?string $serialNumber = null;

    protected ?string $organizationName = null;

    protected ?string $passTypeIdentifier = null;

    protected ?string $authenticationToken = null;

    protected ?string $teamIdentifier = null;

    protected ?Color $backgroundColor = null;

    protected ?Color $foregroundColor = null;

    protected ?Color $labelColor = null;

    protected ?string $description = null;

    protected ?Price $totalPrice = null;

    protected ?Collection $wifiDetails = null;

    protected ?Collection $primaryFields = null;

    protected ?Collection $secondaryFields = null;

    protected ?Collection $auxiliaryFields = null;

    protected ?Collection $headerFields = null;

    protected ?Collection $backFields = null;

    protected ?string $downloadName = null;

    /** @var array<int, Barcode> */
    protected array $barcodes = [];

    protected ?Carbon $relevantDate = null;

    protected ?int $maxDistance = null;

    /** @var array<int, Location> */
    protected array $locations = [];

    /** @var array<int, Beacon> */
    protected array $beacons = [];

    protected ?NfcPayload $nfc = null;

    abstract protected static function validator(): ApplePassValidator;

    public static function make(): static
    {
        return new static;
    }

    /** @internal */
    public static function hydrate(MobilePass $model): static
    {
        return new static($model->content, $model->images, $model, $model->locales ?? []);
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

    public function __construct(
        protected array $data = [],
        protected array $images = [],
        protected ?MobilePass $model = null,
        protected array $locales = [],
    ) {
        $this->downloadName = $model?->download_name;

        $this->uncompileContent();
    }

    protected static function appleConfig(string $key): mixed
    {
        return config("mobile-pass.apple.{$key}");
    }

    public function setDownloadName(string $downloadName): static
    {
        $this->downloadName = $downloadName;

        return $this;
    }

    public function setLogoImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['logo'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setIconImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['icon'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setStripImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['strip'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setThumbnailImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['thumbnail'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setBackgroundImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['background'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLogoImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['logo'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteIconImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['icon'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteStripImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['strip'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteThumbnailImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['thumbnail'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteBackgroundImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->images['background'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

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
    ): static {
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
    ): static {
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
    ): static {
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
    ): static {
        return $this->addField($key, $value, FieldType::Back, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);
    }

    protected function makeFieldContent(
        string $key,
        string $value,
        ?string $label = null,
        ?string $changeMessage = null,
        ?DateType $dateStyle = null,
        ?TimeStyleType $timeStyle = null,
        ?bool $showDateAsRelative = null,
    ): FieldContent {
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

        if ($showDateAsRelative) {
            $field->showDateAsRelative();
        }

        return $field;
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
    ): static {
        $field = $this->makeFieldContent($key, $value, $label, $changeMessage, $dateStyle, $timeStyle, $showDateAsRelative);

        $this->storeField($type->value, $field);

        return $this;
    }

    protected function storeField(string $property, FieldContent $field): void
    {
        $this->{$property} ??= collect();
        $this->{$property}[$field->key] = $field;
    }

    /**
     * The builder properties that hold field collections, so `updateField()` reaches all of them.
     *
     * @return array<int, string>
     */
    protected function fieldProperties(): array
    {
        return array_column(FieldType::cases(), 'value');
    }

    public function updateField(
        string $key,
        string $value,
        ?string $changeMessage = null,
        ?string $label = null,
    ): static {
        foreach ($this->fieldProperties() as $property) {
            if ($this->{$property} === null) {
                continue;
            }

            $this->{$property} = $this->{$property}->map(
                fn (FieldContent $field) => $field->key === $key
                    ? $this->applyFieldUpdate($field, $value, $changeMessage, $label)
                    : $field,
            );
        }

        return $this;
    }

    protected function applyFieldUpdate(
        FieldContent $field,
        string $value,
        ?string $changeMessage,
        ?string $label,
    ): FieldContent {
        $field->withValue($value);

        if ($changeMessage !== null) {
            $field->showMessageWhenChanged($changeMessage);
        }

        if ($label !== null) {
            $field->withLabel($label);
        }

        return $field;
    }

    public function setSerialNumber(string $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function setOrganizationName(string $organizationName): static
    {
        $this->organizationName = $organizationName;

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setBackgroundColor(string $hex): static
    {
        $this->backgroundColor = Color::makeFromHex($hex);

        return $this;
    }

    public function setForegroundColor(string $hex): static
    {
        $this->foregroundColor = Color::makeFromHex($hex);

        return $this;
    }

    public function setLabelColor(string $hex): static
    {
        $this->labelColor = Color::makeFromHex($hex);

        return $this;
    }

    /**
     * The total price for the pass.
     */
    public function setTotalPrice(Price $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function addWifiNetwork(string $ssid, string $password): static
    {
        $this->wifiDetails ??= collect();
        $this->wifiDetails->push(new WifiNetwork($ssid, $password));

        return $this;
    }

    public function setBarcode(BarcodeType $format, string $message, ?string $altText = null): static
    {
        $this->barcodes = [$this->buildBarcode($format, $message, $altText)];

        return $this;
    }

    /** Appends a barcode to the pass, keeping any barcodes already set. */
    public function addBarcode(BarcodeType $format, string $message, ?string $altText = null): self
    {
        $this->barcodes[] = $this->buildBarcode($format, $message, $altText);

        return $this;
    }

    protected function buildBarcode(BarcodeType $format, string $message, ?string $altText): Barcode
    {
        $barcode = Barcode::make($format, $message);

        if ($altText !== null) {
            $barcode->withAltText($altText);
        }

        return $barcode;
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

    public function setRelevantDate(Carbon $date): static
    {
        $this->relevantDate = $date;

        return $this;
    }

    public function addLocation(
        float $latitude,
        float $longitude,
        ?float $altitude = null,
        ?string $relevantText = null,
    ): static {
        $this->locations[] = new Location($latitude, $longitude, $altitude, $relevantText);

        return $this;
    }

    public function addBeacon(
        string $proximityUUID,
        ?int $major = null,
        ?int $minor = null,
        ?string $relevantText = null,
    ): static {
        $this->beacons[] = new Beacon($proximityUUID, $major, $minor, $relevantText);

        return $this;
    }

    public function setMaxDistance(int $meters): static
    {
        $this->maxDistance = $meters;

        return $this;
    }

    public function addLocaleStrings(string $language, array $strings): static
    {
        $existing = $this->locales[$language]['strings'] ?? [];
        $this->locales[$language]['strings'] = array_merge($existing, $strings);

        return $this;
    }

    public function setLocaleLogoImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['logo'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setLocaleIconImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['icon'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setLocaleStripImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['strip'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setLocaleThumbnailImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['thumbnail'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setLocaleBackgroundImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['background'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleLogoImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['logo'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteLocaleIconImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['icon'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteLocaleStripImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['strip'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteLocaleThumbnailImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['thumbnail'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setRemoteLocaleBackgroundImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['background'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setNfc(
        string $message,
        string $encryptionPublicKey,
        bool $requiresAuthentication = false,
    ): static {
        $this->nfc = new NfcPayload($message, $encryptionPublicKey, $requiresAuthentication);

        return $this;
    }

    protected function addImagesToFile(PKPass $pkPass): PKPass
    {
        foreach ($this->images as $filename => $image) {
            if (! $image instanceof Image) {
                $image = Image::fromArray($image);
            }

            $addFile = $image->isRemote ? 'addRemoteFile' : 'addFile';

            if ($image->x1Path) {
                $pkPass->{$addFile}($image->x1Path, "{$filename}.png");
            }

            if ($image->x2Path) {
                $pkPass->{$addFile}($image->x2Path, "{$filename}@2x.png");
            }

            if ($image->x3Path) {
                $pkPass->{$addFile}($image->x3Path, "{$filename}@3x.png");
            }
        }

        return $pkPass;
    }

    protected function addLocaleDataToPass(PKPass $pkPass): void
    {
        foreach ($this->locales as $language => $localeData) {
            if (! empty($localeData['strings'])) {
                $pkPass->addLocaleStrings($language, $localeData['strings']);
            }

            foreach ($localeData['images'] ?? [] as $name => $image) {
                if (! $image instanceof Image) {
                    $image = Image::fromArray($image);
                }
                $method = $image->isRemote ? 'addLocaleRemoteFile' : 'addLocaleFile';
                if ($image->x1Path) {
                    $pkPass->{$method}($language, $image->x1Path, "{$name}.png");
                }
                if ($image->x2Path) {
                    $pkPass->{$method}($language, $image->x2Path, "{$name}@2x.png");
                }
                if ($image->x3Path) {
                    $pkPass->{$method}($language, $image->x3Path, "{$name}@3x.png");
                }
            }
        }
    }

    public static function getCertificatePath(): string
    {
        $contents = self::appleConfig('certificate');

        if (empty($contents)) {
            return self::appleConfig('certificate_path');
        }

        $hash = md5($contents);

        $path = sys_get_temp_dir()."/LaravelMobilePass-{$hash}.p12";

        if (file_exists($path)) {
            return $path;
        }

        file_put_contents($path, base64_decode($contents));

        return $path;
    }

    public static function getCertificatePassword(): string
    {
        return self::appleConfig('certificate_password');
    }

    public function save(): MobilePass
    {
        if ($this->model) {
            $this->serialNumber = $this->model->pass_serial;

            $this->model->update([
                'content' => $this->data(),
                'images' => $this->images,
                'locales' => empty($this->locales) ? null : $this->locales,
                'download_name' => $this->downloadName,
            ]);

            return $this->model;
        }

        $content = $this->data();

        $mobilePassClass = Config::mobilePassModel();

        return $mobilePassClass::query()->create([
            'pass_serial' => $this->serialNumber,
            'type' => $this->type->value,
            'platform' => static::platform(),
            'builder_name' => static::name(),
            'content' => $content,
            'images' => $this->images,
            'locales' => empty($this->locales) ? null : $this->locales,
            'download_name' => $this->downloadName,
        ]);
    }

    public function data(): array
    {
        $configuredOrganizationName = self::appleConfig('organization_name');

        if (empty($this->organizationName)) {
            if (! empty($configuredOrganizationName)) {
                $this->setOrganizationName($configuredOrganizationName);
            }
        }

        if (empty($this->serialNumber)) {
            $this->serialNumber = (string) Str::uuid();
        }

        $compiledData = array_filter(
            $this->compileData(),
            fn ($value) => ! empty($value),
        );

        return $this->validator()->validate($compiledData);
    }

    public function generate(): string
    {
        try {
            $pkPass = new PKPass(
                self::getCertificatePath(),
                self::getCertificatePassword(),
            );

            $pkPass->setData($this->data());

            $this->addImagesToFile($pkPass);
            $this->addLocaleDataToPass($pkPass);

            return $pkPass->create(output: false);
        } catch (PKPassException $exception) {
            throw InvalidCertificate::fromPkPassException($exception);
        }
    }

    protected function compileSemantics(): array
    {
        return array_filter([
            'totalPrice' => $this->totalPrice?->toArray(),
            'wifiAccess' => $this->wifiDetails?->toArray(),
        ], fn ($value) => $value !== null);
    }

    protected function compileData(): array
    {
        $barcodes = empty($this->barcodes) ? null : array_map(
            fn (Barcode $barcode) => $barcode->toArray(),
            $this->barcodes,
        );

        return array_merge($this->data, array_filter([
            'formatVersion' => 1,
            'organizationName' => $this->organizationName,
            'passTypeIdentifier' => self::appleConfig('type_identifier'),
            'serialNumber' => $this->serialNumber,
            'authenticationToken' => self::appleConfig('webservice.secret'),
            'webServiceURL' => $this->webServiceURL(),
            'teamIdentifier' => self::appleConfig('team_identifier'),
            'description' => $this->description,
            'semantics' => $this->compileSemantics(),
            'backgroundColor' => (string) $this->backgroundColor,
            'foregroundColor' => (string) $this->foregroundColor,
            'labelColor' => (string) $this->labelColor,
            'barcode' => $barcodes === null ? null : Arr::last($barcodes),
            'barcodes' => $barcodes,
            'relevantDate' => $this->relevantDate?->toIso8601String(),
            'locations' => empty($this->locations) ? null : array_map(
                fn (Location $location) => $location->toArray(),
                $this->locations,
            ),
            'maxDistance' => $this->maxDistance,
            'beacons' => empty($this->beacons) ? null : array_map(
                fn (Beacon $beacon) => $beacon->toArray(),
                $this->beacons,
            ),
            'nfc' => $this->nfc?->toArray(),
            'userInfo' => [
                'passType' => $this->type->value,
            ],
        ]));
    }

    protected function webServiceURL(): ?string
    {
        $host = $this->resolveWebServiceHost();

        if ($host === null) {
            return null;
        }

        return rtrim($host, '/').'/passkit';
    }

    protected function resolveWebServiceHost(): ?string
    {
        $configuredHost = self::appleConfig('webservice.host');

        if (is_string($configuredHost)) {
            if ($configuredHost !== '') {
                if (! str_starts_with($configuredHost, 'https://')) {
                    throw InvalidConfig::webserviceHostMustBeHttps($configuredHost);
                }

                return $configuredHost;
            }
        }

        $appUrl = (string) config('app.url');

        if (! str_starts_with($appUrl, 'https://')) {
            return null;
        }

        return $appUrl;
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

    /** @param  array<string, mixed>  $semantics */
    protected function parseSemanticDate(array $semantics, string $key): ?Carbon
    {
        if (empty($semantics[$key])) {
            return null;
        }

        return Carbon::parse($semantics[$key]);
    }

    /** @return array<int, Barcode> */
    protected function uncompileBarcodes(): array
    {
        if (! empty($this->data['barcodes'])) {
            return array_map(
                fn (array $barcode) => Barcode::fromArray($barcode),
                $this->data['barcodes'],
            );
        }

        if (empty($this->data['barcode'])) {
            return [];
        }

        return [Barcode::fromArray($this->data['barcode'])];
    }

    protected function uncompileContent(): void
    {
        $this->organizationName = $this->data['organizationName'] ?? null;
        $this->passTypeIdentifier = $this->data['passTypeIdentifier'] ?? null;
        $this->serialNumber = $this->data['serialNumber'] ?? null;
        $this->authenticationToken = $this->data['authenticationToken'] ?? null;
        $this->teamIdentifier = $this->data['teamIdentifier'] ?? null;
        $this->description = $this->data['description'] ?? null;
        $this->backgroundColor = Color::makeFromRgbString($this->data['backgroundColor'] ?? null);
        $this->foregroundColor = Color::makeFromRgbString($this->data['foregroundColor'] ?? null);
        $this->labelColor = Color::makeFromRgbString($this->data['labelColor'] ?? null);

        $this->barcodes = $this->uncompileBarcodes();

        $this->relevantDate = empty($this->data['relevantDate'])
            ? null
            : Carbon::parse($this->data['relevantDate']);

        $this->locations = array_map(
            fn (array $location) => Location::fromArray($location),
            $this->data['locations'] ?? [],
        );

        $this->maxDistance = $this->data['maxDistance'] ?? null;

        $this->beacons = array_map(
            fn (array $beacon) => Beacon::fromArray($beacon),
            $this->data['beacons'] ?? [],
        );

        $this->nfc = empty($this->data['nfc'])
            ? null
            : NfcPayload::fromArray($this->data['nfc']);

        $this->uncompileSemantics();

        foreach (FieldType::cases() as $fieldType) {
            $this->uncompileFieldSet($fieldType->value);
        }
    }

    protected function uncompileFieldSet(string $fieldSetName): void
    {
        $this->{$fieldSetName} = collect();

        foreach ($this->data[$this->type->value][$fieldSetName] ?? [] as $field) {
            $this->{$fieldSetName}[$field['key']] = FieldContent::fromArray($field);
        }
    }
}
