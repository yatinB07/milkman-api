<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreProductData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store_category_id' => ['sometimes', 'required', 'integer', Rule::exists('store_categories', 'id')->where('store_id', $this->user()?->getKey())],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'image_path' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreProductData
    {
        return StoreProductData::fromArray($this->validated());
    }
}
