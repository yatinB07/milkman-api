<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ZoneData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
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
            'coordinates' => ['sometimes', 'required', 'string'],
            'alias' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ZoneData
    {
        return ZoneData::fromArray($this->validated());
    }
}
