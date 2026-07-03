<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreOrderHistoryQueryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderHistoryRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.StoreOrderHistoryQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): StoreOrderHistoryQueryData
    {
        return StoreOrderHistoryQueryData::fromArray($this->validated());
    }
}
