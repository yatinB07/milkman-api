<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CouponCheckData;
use Illuminate\Foundation\Http\FormRequest;

class CouponCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'coupon_id' => ['required', 'integer', 'exists:coupons,id'],
        ];
    }

    public function toData(): CouponCheckData
    {
        return CouponCheckData::fromArray($this->validated());
    }
}
