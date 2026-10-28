<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class GenericClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
        ];
    }

    /**
     * genericClass has no `locations` field.
     *
     * @return array<string, array<int, string>>
     */
    protected function moduleRules(): array
    {
        return array_filter(
            parent::moduleRules(),
            fn (string $key) => ! str_starts_with($key, 'locations'),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
