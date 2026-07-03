<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreFaqData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'required', 'string'],
            'answer' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreFaqData
    {
        return StoreFaqData::fromArray($this->validated());
    }
}
