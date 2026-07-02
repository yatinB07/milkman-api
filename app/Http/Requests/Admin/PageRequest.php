<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\PageData;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): PageData
    {
        return PageData::fromArray($this->validated());
    }
}
