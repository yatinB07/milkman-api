<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\SubscriptionOrderItemData;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'subscription_order_id' => ['required', 'integer', 'exists:subscription_orders,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'product_title' => ['required', 'string', 'max:255'],
            'discount' => ['required', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'total_deliveries' => ['nullable', 'integer', 'min:0'],
            'total_dates' => ['nullable', 'string'],
            'completed_dates' => ['nullable', 'string'],
            'selected_days' => ['nullable', 'string'],
            'time_slot' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): SubscriptionOrderItemData
    {
        return SubscriptionOrderItemData::fromArray($this->validated());
    }
}
