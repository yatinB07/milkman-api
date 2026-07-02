<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\ListCustomerQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListCustomerResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ListCustomerQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): ListCustomerQueryData
    {
        return ListCustomerQueryData::fromArray($this->validated());
    }
}
