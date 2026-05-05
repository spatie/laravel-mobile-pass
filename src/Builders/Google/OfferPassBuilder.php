<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\OfferObjectValidator;
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

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setRedemptionCode(string $code): self
    {
        $this->redemptionCode = $code;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return $this->filterEmpty([
            'title' => $this->title,
            'redemptionCode' => $this->redemptionCode,
        ]);
    }
}
