<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'profile_image_path' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
            'registered_at' => ['nullable', 'date'],
            'referral_code' => ['nullable', 'string', 'max:255', 'unique:customers,referral_code'],
            'parent_referral_code' => ['nullable', 'string', 'max:255'],
            'wallet_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'email_verified_at' => ['nullable', 'date'],
        ];
    }

    public function toData(): CustomerData
    {
        return CustomerData::fromArray($this->validated());
    }
}
