<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ProductImageData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'required', 'integer', 'exists:stores,id'],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'image_path' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ProductImageData
    {
        return ProductImageData::fromArray($this->validated());
    }
}
