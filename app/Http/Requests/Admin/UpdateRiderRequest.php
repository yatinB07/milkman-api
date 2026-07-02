<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\RiderData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'nullable', 'integer', 'exists:stores,id'],
            'image_path' => ['sometimes', 'nullable', 'string'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('riders', 'email')->ignore($this->route('rider')),
            ],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:8'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:32'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'joined_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function toData(): RiderData
    {
        return RiderData::fromArray($this->validated());
    }
}
