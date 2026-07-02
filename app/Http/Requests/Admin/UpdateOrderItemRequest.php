<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\OrderItemData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'required', 'integer', 'exists:orders,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'product_title' => ['sometimes', 'required', 'string', 'max:255'],
            'discount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'variant_title' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): OrderItemData
    {
        return OrderItemData::fromArray($this->validated());
    }
}
