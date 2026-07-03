<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreCouponData;
use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
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

    public function toData(): StoreCouponData
    {
        return StoreCouponData::fromArray($this->validated());
    }
}
