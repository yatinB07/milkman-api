<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerHomeQueryData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerHomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.CustomerHomeQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): CustomerHomeQueryData
    {
        return CustomerHomeQueryData::fromArray($this->validated());
    }
}
