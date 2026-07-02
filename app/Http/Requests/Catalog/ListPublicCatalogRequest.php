<?php

namespace App\Http\Requests\Catalog;

use App\Data\Catalog\PublicListQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListPublicCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.PublicListQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): PublicListQueryData
    {
        return PublicListQueryData::fromArray($this->validated());
    }
}
