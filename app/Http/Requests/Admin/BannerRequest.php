<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\BannerData;
use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
            'image_path' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): BannerData
    {
        return BannerData::fromArray($this->validated());
    }
}
