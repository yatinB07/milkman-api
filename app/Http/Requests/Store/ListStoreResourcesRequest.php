<?php

namespace App\Http\Requests\Store;

use App\Data\Store\ListStoreQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListStoreResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ListStoreQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): ListStoreQueryData
    {
        return ListStoreQueryData::fromArray($this->validated());
    }
}
