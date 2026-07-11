<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'directory' => [
                'required',
                'string',
                Rule::in(['categories', 'products', 'store-categories', 'stores', 'uploads']),
            ],
            'file' => ['required', 'image', 'max:5120'],
        ];
    }
}
