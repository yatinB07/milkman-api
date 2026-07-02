<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\PaymentMethodData;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
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
            'attributes' => ['sometimes', 'nullable', 'array'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_visible' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): PaymentMethodData
    {
        return PaymentMethodData::fromArray($this->validated());
    }
}
