<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreGalleryImageData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreGalleryImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'required', 'integer', 'exists:stores,id'],
            'image_path' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreGalleryImageData
    {
        return StoreGalleryImageData::fromArray($this->validated());
    }
}
