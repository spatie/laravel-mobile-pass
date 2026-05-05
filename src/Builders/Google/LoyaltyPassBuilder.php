<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\LoyaltyObjectValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class LoyaltyPassBuilder extends GooglePassBuilder
{
    protected PassType $type = PassType::StoreCard;

    protected ?string $accountId = null;

    protected ?string $accountName = null;

    protected ?int $balanceMicros = null;

    protected ?string $balanceString = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new LoyaltyObjectValidator;
    }

    protected static function classResource(): string
    {
        return 'loyaltyClass';
    }

    protected static function objectResource(): string
    {
        return 'loyaltyObject';
    }

    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function setAccountName(string $accountName): self
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function setBalanceMicros(int $micros): self
    {
        $this->balanceMicros = $micros;

        return $this;
    }

    public function setBalanceString(string $value): self
    {
        $this->balanceString = $value;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $balance = $this->filterEmpty([
            'micros' => $this->balanceMicros,
            'string' => $this->balanceString,
        ]);

        $loyaltyPoints = $this->filterEmpty([
            'balance' => $balance,
        ]);

        return $this->filterEmpty([
            'accountId' => $this->accountId,
            'accountName' => $this->accountName,
            'loyaltyPoints' => $loyaltyPoints,
        ]);
    }
}
