<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CouponData;
use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
            'image_path' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
            'minimum_amount' => ['required', 'numeric', 'min:0'],
            'value' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): CouponData
    {
        return CouponData::fromArray($this->validated());
    }
}
