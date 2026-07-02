<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\TimeSlotData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
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
            'starts_at' => ['sometimes', 'required', 'date_format:H:i:s'],
            'ends_at' => ['sometimes', 'required', 'date_format:H:i:s', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): TimeSlotData
    {
        return TimeSlotData::fromArray($this->validated());
    }
}
