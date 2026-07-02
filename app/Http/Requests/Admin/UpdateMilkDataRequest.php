<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\MilkDataData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMilkDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'data' => ['sometimes', 'required', 'string'],
        ];
    }

    public function toData(): MilkDataData
    {
        return MilkDataData::fromArray($this->validated());
    }
}
