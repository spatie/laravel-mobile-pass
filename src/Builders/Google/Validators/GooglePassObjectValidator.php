<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

abstract class GooglePassObjectValidator
{
    /** @return array<string, array<int, string>> */
    abstract protected function rules(): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        $validator = validator($payload, $this->rules());

        if ($validator->fails()) {
            throw new InvalidPass($validator);
        }

        return $validator->validated();
    }
}
