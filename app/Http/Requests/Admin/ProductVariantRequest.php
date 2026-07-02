<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ProductVariantData;
use Illuminate\Foundation\Http\FormRequest;

class ProductVariantRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'subscribe_price' => ['required', 'numeric', 'min:0'],
            'normal_price' => ['required', 'numeric', 'min:0'],
            'title' => ['required', 'string', 'max:255'],
            'discount' => ['sometimes', 'numeric', 'min:0'],
            'is_out_of_stock' => ['sometimes', 'boolean'],
            'is_subscription_required' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ProductVariantData
    {
        return ProductVariantData::fromArray($this->validated());
    }
}
