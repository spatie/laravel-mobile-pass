<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\TextModule;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\OfferObjectValidator;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\PassType;

class OfferPassBuilder extends GooglePassBuilder
{
    protected PassType $type = PassType::Coupon;

    protected ?string $title = null;

    protected ?string $redemptionCode = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new OfferObjectValidator;
    }

    protected static function classResource(): string
    {
        return 'offerClass';
    }

    protected static function objectResource(): string
    {
        return 'offerObject';
    }

    /**
     * @deprecated offerObject has no title field, so this value is not sent to Google.
     *             The offer title lives on the class: use OfferPassClass::setTitle().
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * offerObject has no redemption code field. When no barcode is set, the code
     * becomes a QR barcode (with the code as its readable text). When a barcode
     * is set and carries a different value, the code is added as a
     * "Redemption code" text module instead.
     */
    public function setRedemptionCode(string $code): self
    {
        $this->redemptionCode = $code;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return [];
    }

    /** @return array<string, mixed>|null */
    protected function compileBarcode(): ?array
    {
        if ($this->barcode === null && $this->redemptionCode !== null) {
            return [
                'type' => $this->translateBarcodeType(BarcodeType::Qr),
                'value' => $this->redemptionCode,
                'alternateText' => $this->redemptionCode,
            ];
        }

        return parent::compileBarcode();
    }

    /** @return array<string, mixed> */
    protected function compileModules(): array
    {
        if (
            $this->redemptionCode === null
            || $this->barcode === null
            || $this->barcode->message === $this->redemptionCode
        ) {
            return parent::compileModules();
        }

        $modules = parent::compileModules();

        $modules['textModulesData'][] = (new TextModule(
            'Redemption code',
            $this->redemptionCode,
            'redemptionCode',
        ))->toArray();

        return $modules;
    }
}
