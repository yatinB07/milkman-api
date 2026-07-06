<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerEmailAvailabilityData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerEmailAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function toData(): CustomerEmailAvailabilityData
    {
        return CustomerEmailAvailabilityData::fromArray($this->validated());
    }
}
