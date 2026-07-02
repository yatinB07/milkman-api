<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerStoreSearchQueryData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreSearchRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.CustomerStoreSearchQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): CustomerStoreSearchQueryData
    {
        return CustomerStoreSearchQueryData::fromArray($this->validated());
    }
}
