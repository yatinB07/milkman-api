<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_image_path' => ['sometimes', 'nullable', 'string'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($this->route('customer')),
            ],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:8'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:32'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'registered_at' => ['sometimes', 'nullable', 'date'],
            'referral_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers', 'referral_code')->ignore($this->route('customer')),
            ],
            'parent_referral_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'wallet_balance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'email_verified_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function toData(): CustomerData
    {
        return CustomerData::fromArray($this->validated());
    }
}
