<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\FaqData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
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
            'question' => ['sometimes', 'required', 'string'],
            'answer' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): FaqData
    {
        return FaqData::fromArray($this->validated());
    }
}
