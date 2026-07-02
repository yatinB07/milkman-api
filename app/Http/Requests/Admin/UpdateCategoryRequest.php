<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CategoryData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'cover_path' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): CategoryData
    {
        return CategoryData::fromArray($this->validated());
    }
}
