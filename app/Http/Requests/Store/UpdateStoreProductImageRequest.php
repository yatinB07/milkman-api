<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreProductImageData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'product_id' => ['sometimes', 'required', 'integer', Rule::exists('products', 'id')->where('store_id', $this->user()?->getKey())],
            'image_path' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreProductImageData
    {
        return StoreProductImageData::fromArray($this->validated());
    }
}
