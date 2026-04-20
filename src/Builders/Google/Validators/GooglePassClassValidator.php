<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

abstract class GooglePassClassValidator
{
    /** @return array<string, array<int, string>> */
    abstract protected function rules(): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        return validator($payload, $this->rules())->validate();
    }
}
