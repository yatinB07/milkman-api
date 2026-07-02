<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\DeliveryOptionData;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'title' => ['required', 'string', 'max:255'],
            'delivery_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): DeliveryOptionData
    {
        return DeliveryOptionData::fromArray($this->validated());
    }
}
