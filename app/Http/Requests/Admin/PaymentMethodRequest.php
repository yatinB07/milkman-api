<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\PaymentMethodData;
use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
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
            'image_path' => ['nullable', 'string'],
            'attributes' => ['nullable', 'array'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'is_visible' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): PaymentMethodData
    {
        return PaymentMethodData::fromArray($this->validated());
    }
}
