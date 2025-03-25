<?php

namespace Spatie\LaravelMobilePass\Builders;

use Illuminate\Support\Collection;
use PKPass\PKPass;
use Spatie\LaravelMobilePass\Entities\FieldContent;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Entities\Price;
use Spatie\LaravelMobilePass\Entities\WifiNetwork;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Validators\PassValidator;

abstract class PassBuilder
{
    protected ?array $data = null;

    protected PassType $type;

    protected ?string $serialNumber = null;

    protected ?string $organisationName = null;

    protected ?string $description = null;

    protected ?Price $totalPrice = null;

    protected ?Collection $wifiDetails = null;

    protected ?Collection $primaryFields = null;

    protected ?Collection $secondaryFields = null;

    protected ?Collection $auxiliaryFields = null;

    protected ?Collection $headerFields = null;

    protected ?Collection $backFields = null;

    protected array $images = [];

    abstract protected static function validator(): PassValidator;

    public static function make(array $data = [], array $images = []): static
    {
        return new static($data, $images);
    }

    public function __construct(array $data = [], array $images = [])
    {
        $this->data = $data;
        $this->images = $images;
    }

    public function setLogoImage(Image $image): self
    {
        $this->images['logo'] = $image;

        return $this;
    }

    public function setIconImage(Image $image): self
    {
        $this->images['icon'] = $image;

        return $this;
    }

    public function setPrimaryFields(FieldContent ...$primaryField): self
    {
        $this->primaryFields = collect($primaryField);

        return $this;
    }

    public function setSecondaryFields(FieldContent ...$secondaryField): self
    {
        $this->secondaryFields = collect($secondaryField);

        return $this;
    }

    public function setAuxiliaryFields(FieldContent ...$auxiliaryField): self
    {
        $this->auxiliaryFields = collect($auxiliaryField);

        return $this;
    }

    public function setHeaderFields(FieldContent ...$headerField): self
    {
        $this->headerFields = collect($headerField);

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

    protected function compileSemantics(): ?array
    {
        return array_filter([
            'totalPrice' => $this->totalPrice?->toArray(),
            'wifiAccess' => $this->wifiDetails?->toArray(),
        ]);
    }

    protected function compileData(): array
    {
        return array_merge($this->data ?? [], array_filter([
            'formatVersion' => 1,
            'organizationName' => $this->organisationName,
            'passTypeIdentifier' => config('mobile-pass.type_identifier'),
            'serialNumber' => $this->serialNumber,
            'authenticationToken' => config('mobile-pass.webservice_secret'),
            'teamIdentifier' => config('mobile-pass.team_identifier'),
            'description' => $this->description,
            'semantics' => $this->compileSemantics(),
        ]));
    }

    protected function addImagesToFile(PKPass $pkPass): PKPass
    {
        foreach ($this->images as $filename => $image) {
            // The $image Image entity could contain up to three
            // images in different resolutions.

            if (! $image instanceof  Image) {
               $image = Image::make($image['x1Path'], $image['x2Path'], $image['x3Path']);
            }

            if ($image->x1Path) {
                $pkPass->addFile($image->x1Path, "$filename.png");
            }

            if ($image->x2Path) {
                $pkPass->addFile($image->x2Path, "$filename@2x.png");
            }

            if ($image->x3Path) {
                $pkPass->addFile($image->x3Path, "$filename@3x.png");
            }
        }

        return $pkPass;
    }

    protected static function getCertificatePath(): string
    {
        if (! empty(config('mobile-pass.apple.certificate_contents'))) {
            $path = __DIR__.'/../../tmp/Cert.p12';

            file_put_contents(
                $path,
                base64_decode(
                    config('mobile-pass.apple.certificate_contents')
                )
            );

            return $path;
        }

        return config('mobile-pass.apple.certificate_path');
    }

    public function create(): MobilePass
    {
        return MobilePass::create([
            'builder_class' => static::class,
            'content' => $this->data(),
            'images' => $this->images,
        ]);
    }

    public function data(): array
    {
        if (empty($this->organisationName)) {
            $this->setOrganisationName(
                config('mobile-pass.organisation_name')
            );
        }

        // Remove any null keys or keys where the value is an empty array.
        // TODO: do this recursively.
        $compiledData = array_filter(
            $this->compileData(),
            fn ($value) => ! empty($value)
        );


        $data = $this->validator()->validate(
            $compiledData
        );

        // The icon image is always required.
        // TODO: validate this.

        return $data;
    }

    public function generate()
    {


        $pkPass = new PKPass(
            self::getCertificatePath(),
            config('mobile-pass.apple.certificate_password'),
        );

        $pkPass->setData($this->data());

        $this->addImagesToFile($pkPass);

        return $pkPass->create(output: false);
    }
}
