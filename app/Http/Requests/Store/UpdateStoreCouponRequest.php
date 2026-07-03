<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreCouponData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'image_path' => ['sometimes', 'nullable', 'string'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:32'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'minimum_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreCouponData
    {
        return StoreCouponData::fromArray($this->validated());
    }
}
