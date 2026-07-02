<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ListQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListAdminResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ListQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): ListQueryData
    {
        return ListQueryData::fromArray($this->validated());
    }
}
