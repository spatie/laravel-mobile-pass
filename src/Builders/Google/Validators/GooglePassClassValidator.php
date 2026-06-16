<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

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
        $validator = validator($payload, $this->rules() + $this->moduleRules());

        if ($validator->fails()) {
            throw new InvalidPass($validator);
        }

        return $validator->validated();
    }

    /**
     * Module fields are shared by every Google class type, so they live here
     * instead of being repeated in each class validator.
     *
     * @return array<string, array<int, string>>
     */
    protected function moduleRules(): array
    {
        return [
            'locations' => ['nullable', 'array'],
            'locations.*.latitude' => ['nullable', 'numeric'],
            'locations.*.longitude' => ['nullable', 'numeric'],
            'linksModuleData' => ['nullable', 'array'],
            'linksModuleData.uris' => ['nullable', 'array'],
            'linksModuleData.uris.*.uri' => ['nullable', 'string'],
            'linksModuleData.uris.*.description' => ['nullable', 'string'],
            'textModulesData' => ['nullable', 'array'],
            'textModulesData.*.header' => ['nullable', 'string'],
            'textModulesData.*.body' => ['nullable', 'string'],
            'textModulesData.*.id' => ['nullable', 'string'],
            'imageModulesData' => ['nullable', 'array'],
            'imageModulesData.*.mainImage' => ['nullable', 'array'],
            'imageModulesData.*.id' => ['nullable', 'string'],
        ];
    }
}
