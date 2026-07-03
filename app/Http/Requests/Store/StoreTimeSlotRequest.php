<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreTimeSlotData;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date_format:H:i:s'],
            'ends_at' => ['required', 'date_format:H:i:s', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreTimeSlotData
    {
        return StoreTimeSlotData::fromArray($this->validated());
    }
}
