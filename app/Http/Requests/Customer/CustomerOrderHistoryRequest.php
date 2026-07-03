<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerOrderHistoryQueryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerOrderHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['current', 'past'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.CustomerOrderHistoryQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): CustomerOrderHistoryQueryData
    {
        return CustomerOrderHistoryQueryData::fromArray($this->validated());
    }
}
