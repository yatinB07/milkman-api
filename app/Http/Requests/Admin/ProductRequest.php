<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ProductData;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'store_category_id' => ['required', 'integer', 'exists:store_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ProductData
    {
        return ProductData::fromArray($this->validated());
    }
}
