<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreCategoryData;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreCategoryData
    {
        return StoreCategoryData::fromArray($this->validated());
    }
}
