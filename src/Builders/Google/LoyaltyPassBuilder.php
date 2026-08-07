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

    protected ?string $balanceLabel = null;

    protected ?int $secondaryBalanceMicros = null;

    protected ?string $secondaryBalanceString = null;

    protected ?string $secondaryBalanceLabel = null;

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

    public function setBalanceLabel(string $label): self
    {
        $this->balanceLabel = $label;

        return $this;
    }

    public function setSecondaryBalanceMicros(int $micros): self
    {
        $this->secondaryBalanceMicros = $micros;

        return $this;
    }

    public function setSecondaryBalanceString(string $value): self
    {
        $this->secondaryBalanceString = $value;

        return $this;
    }

    public function setSecondaryBalanceLabel(string $label): self
    {
        $this->secondaryBalanceLabel = $label;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return $this->filterEmpty([
            'accountId' => $this->accountId,
            'accountName' => $this->accountName,
            'loyaltyPoints' => $this->compilePoints(
                $this->balanceLabel,
                $this->balanceMicros,
                $this->balanceString,
            ),
            'secondaryLoyaltyPoints' => $this->compilePoints(
                $this->secondaryBalanceLabel,
                $this->secondaryBalanceMicros,
                $this->secondaryBalanceString,
            ),
        ]);
    }

    /** @return array<string, mixed> */
    protected function compilePoints(?string $label, ?int $micros, ?string $string): array
    {
        return $this->filterEmpty([
            'label' => $label,
            'balance' => $this->filterEmpty([
                'micros' => $micros,
                'string' => $string,
            ]),
        ]);
    }
}
