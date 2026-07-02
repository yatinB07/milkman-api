<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\SubscriptionOrderData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionOrderRequest extends FormRequest
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
            'customer_id' => ['sometimes', 'required', 'integer', 'exists:customers,id'],
            'ordered_at' => ['nullable', 'date'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'address' => ['sometimes', 'required', 'string'],
            'landmark' => ['nullable', 'string'],
            'delivery_charge' => ['sometimes', 'required', 'numeric', 'min:0'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'coupon_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'total' => ['sometimes', 'required', 'numeric', 'min:0'],
            'subtotal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string'],
            'admin_status' => ['sometimes', 'required', 'integer', 'min:0'],
            'rider_id' => ['nullable', 'integer', 'exists:riders,id'],
            'wallet_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_mobile' => ['sometimes', 'required', 'string', 'max:32'],
            'status' => ['sometimes', 'required', 'string', 'max:255'],
            'rejection_comment' => ['nullable', 'string'],
            'time_slot' => ['nullable', 'string', 'max:255'],
            'order_type' => ['sometimes', 'required', 'string', 'max:255'],
            'is_rated' => ['sometimes', 'required', 'boolean'],
            'reviewed_at' => ['nullable', 'date'],
            'total_rating' => ['sometimes', 'required', 'integer', 'min:0'],
            'rating_text' => ['nullable', 'string'],
            'commission_percent' => ['sometimes', 'required', 'numeric', 'min:0'],
            'store_charge' => ['sometimes', 'required', 'numeric', 'min:0'],
            'internal_status' => ['sometimes', 'required', 'integer', 'min:0'],
            'signature_path' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function toData(): SubscriptionOrderData
    {
        return SubscriptionOrderData::fromArray($this->validated());
    }
}
