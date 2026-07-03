<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerSubscriptionOrderData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerSubscriptionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'address' => ['required', 'string', 'max:1000'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'delivery_charge' => ['required', 'numeric', 'min:0'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'coupon_amount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'wallet_amount' => ['nullable', 'numeric', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_mobile' => ['required', 'string', 'max:32'],
            'order_type' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.product_title' => ['required', 'string', 'max:255'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.image_path' => ['nullable', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.variant_title' => ['required', 'string', 'max:255'],
            'items.*.starts_at' => ['required', 'date'],
            'items.*.total_deliveries' => ['required', 'integer', 'min:1', 'max:366'],
            'items.*.selected_days' => ['required', 'array', 'min:1'],
            'items.*.selected_days.*' => ['integer', 'min:0', 'max:6'],
            'items.*.time_slot' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): CustomerSubscriptionOrderData
    {
        return CustomerSubscriptionOrderData::fromArray($this->validated());
    }
}
