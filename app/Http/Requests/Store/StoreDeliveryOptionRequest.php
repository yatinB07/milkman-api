<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreDeliveryOptionData;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOptionRequest extends FormRequest
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
            'delivery_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreDeliveryOptionData
    {
        return StoreDeliveryOptionData::fromArray($this->validated());
    }
}
