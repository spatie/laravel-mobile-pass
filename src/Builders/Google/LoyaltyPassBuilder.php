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

    /**
     * Google's LoyaltyPointsBalance is a oneof: exactly one of string, int,
     * double or money. The last balance setter called wins.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $balance = null;

    protected ?string $balanceLabel = null;

    /** @var array<string, mixed>|null */
    protected ?array $secondaryBalance = null;

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

    public function setBalanceString(string $value): self
    {
        $this->balance = ['string' => $value];

        return $this;
    }

    public function setBalanceInt(int $value): self
    {
        $this->balance = ['int' => $value];

        return $this;
    }

    public function setBalanceDouble(float $value): self
    {
        $this->balance = ['double' => $value];

        return $this;
    }

    /** @param  int  $micros  The amount in millionths of the currency unit, e.g. 12_500_000 for 12.50. */
    public function setBalanceMoney(int $micros, string $currencyCode): self
    {
        $this->balance = $this->money($micros, $currencyCode);

        return $this;
    }

    /**
     * @deprecated Google has no "micros" balance. Without a currency code the value is
     *             sent as an int balance; with one it is sent as a money balance.
     *             Use setBalanceInt() or setBalanceMoney() instead.
     */
    public function setBalanceMicros(int $micros, ?string $currencyCode = null): self
    {
        $this->balance = $currencyCode === null
            ? ['int' => $micros]
            : $this->money($micros, $currencyCode);

        return $this;
    }

    public function setBalanceLabel(string $label): self
    {
        $this->balanceLabel = $label;

        return $this;
    }

    public function setSecondaryBalanceString(string $value): self
    {
        $this->secondaryBalance = ['string' => $value];

        return $this;
    }

    public function setSecondaryBalanceInt(int $value): self
    {
        $this->secondaryBalance = ['int' => $value];

        return $this;
    }

    public function setSecondaryBalanceDouble(float $value): self
    {
        $this->secondaryBalance = ['double' => $value];

        return $this;
    }

    /** @param  int  $micros  The amount in millionths of the currency unit, e.g. 12_500_000 for 12.50. */
    public function setSecondaryBalanceMoney(int $micros, string $currencyCode): self
    {
        $this->secondaryBalance = $this->money($micros, $currencyCode);

        return $this;
    }

    /**
     * @deprecated Google has no "micros" balance. Without a currency code the value is
     *             sent as an int balance; with one it is sent as a money balance.
     *             Use setSecondaryBalanceInt() or setSecondaryBalanceMoney() instead.
     */
    public function setSecondaryBalanceMicros(int $micros, ?string $currencyCode = null): self
    {
        $this->secondaryBalance = $currencyCode === null
            ? ['int' => $micros]
            : $this->money($micros, $currencyCode);

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
            'loyaltyPoints' => $this->compilePoints($this->balanceLabel, $this->balance),
            'secondaryLoyaltyPoints' => $this->compilePoints($this->secondaryBalanceLabel, $this->secondaryBalance),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $balance
     * @return array<string, mixed>
     */
    protected function compilePoints(?string $label, ?array $balance): array
    {
        return $this->filterEmpty([
            'label' => $label,
            'balance' => $balance,
        ]);
    }

    /** @return array{money: array{micros: string, currencyCode: string}} */
    protected function money(int $micros, string $currencyCode): array
    {
        // Money.micros is an int64, which Google's JSON mapping represents as a string.
        return ['money' => ['micros' => (string) $micros, 'currencyCode' => $currencyCode]];
    }
}
