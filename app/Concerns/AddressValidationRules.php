<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait AddressValidationRules
{
    /**
     * Get the validation rules used to validate addresses.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function addressRules(?int $userId = null): array
    {
        return [
            'country' => $this->countryRules(),
            'province' => $this->provinceRules(),
            'city' => $this->cityRules(),
            'district' => $this->districtRules(),
        ];
    }

    /**
     * Get the validation rules used to validate countries.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function countryRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate provinces.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function provinceRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate cities.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function cityRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate districts.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function districtRules(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
