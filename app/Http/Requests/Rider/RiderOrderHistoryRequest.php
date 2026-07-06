<?php

namespace App\Http\Requests\Rider;

use App\Data\Rider\RiderOrderHistoryQueryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RiderOrderHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['current', 'past'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.RiderOrderHistoryQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): RiderOrderHistoryQueryData
    {
        return RiderOrderHistoryQueryData::fromArray($this->validated());
    }
}
