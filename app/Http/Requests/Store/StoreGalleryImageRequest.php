<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreGalleryImageData;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'image_path' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreGalleryImageData
    {
        return StoreGalleryImageData::fromArray($this->validated());
    }
}
