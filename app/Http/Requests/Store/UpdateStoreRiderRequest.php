<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreRiderData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
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
        ];
    }

    public function toData(): StoreRiderData
    {
        return StoreRiderData::fromArray($this->validated());
    }
}
