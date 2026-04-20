<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelMobilePass\Actions\Google\CreateGoogleObjectAction;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;

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

    public function setBarcode(Barcode $barcode): static
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function objectId(): string
    {
        $suffix = $this->objectSuffix ??= (string) Str::uuid();

        return GoogleCredentials::issuerId().'.'.$suffix;
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
        ], $this->compileData()));
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
            BarcodeType::QR => 'QR_CODE',
            BarcodeType::PDF417 => 'PDF_417',
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
