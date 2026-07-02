<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\BannerData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
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
            'image_path' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): BannerData
    {
        return BannerData::fromArray($this->validated());
    }
}
