<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\AdminPasswordData;
use Illuminate\Foundation\Http\FormRequest;

class AdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function toData(): AdminPasswordData
    {
        return AdminPasswordData::fromArray($this->validated());
    }
}
