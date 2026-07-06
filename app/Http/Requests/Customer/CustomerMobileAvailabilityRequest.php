<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerMobileAvailabilityData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerMobileAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'max:8'],
            'mobile' => ['required', 'string', 'max:32'],
        ];
    }

    public function toData(): CustomerMobileAvailabilityData
    {
        return CustomerMobileAvailabilityData::fromArray($this->validated());
    }
}
