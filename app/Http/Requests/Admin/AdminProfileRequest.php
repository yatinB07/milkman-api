<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\AdminProfileData;
use Illuminate\Foundation\Http\FormRequest;

class AdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function toData(): AdminProfileData
    {
        return AdminProfileData::fromArray($this->validated());
    }
}
