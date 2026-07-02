<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\RiderData;
use Illuminate\Foundation\Http\FormRequest;

class RiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'image_path' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:riders,email'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'joined_at' => ['nullable', 'date'],
        ];
    }

    public function toData(): RiderData
    {
        return RiderData::fromArray($this->validated());
    }
}
