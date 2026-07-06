<?php

namespace App\Http\Requests\Rider;

use App\Data\Rider\ListRiderQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListRiderResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ListRiderQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): ListRiderQueryData
    {
        return ListRiderQueryData::fromArray($this->validated());
    }
}
