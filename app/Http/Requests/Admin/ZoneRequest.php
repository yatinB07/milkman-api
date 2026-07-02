<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ZoneData;
use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
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
            'coordinates' => ['required', 'string'],
            'alias' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ZoneData
    {
        return ZoneData::fromArray($this->validated());
    }
}
