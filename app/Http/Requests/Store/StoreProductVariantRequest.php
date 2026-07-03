<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreProductVariantData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('store_id', $this->user()?->getKey())],
            'subscribe_price' => ['required', 'numeric', 'min:0'],
            'normal_price' => ['required', 'numeric', 'min:0'],
            'title' => ['required', 'string', 'max:255'],
            'discount' => ['sometimes', 'numeric', 'min:0'],
            'is_out_of_stock' => ['sometimes', 'boolean'],
            'is_subscription_required' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreProductVariantData
    {
        return StoreProductVariantData::fromArray($this->validated());
    }
}
