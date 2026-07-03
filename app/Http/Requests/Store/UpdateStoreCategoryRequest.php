<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreCategoryData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'image_path' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreCategoryData
    {
        return StoreCategoryData::fromArray($this->validated());
    }
}
