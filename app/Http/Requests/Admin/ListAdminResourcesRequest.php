<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ListQueryData;
use Illuminate\Foundation\Http\FormRequest;

class ListAdminResourcesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->normalizeBooleanQuery('is_active'),
            'is_out_of_stock' => $this->normalizeBooleanQuery('is_out_of_stock'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'is_out_of_stock' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ListQueryData::MAX_PER_PAGE],
        ];
    }

    public function toData(): ListQueryData
    {
        return ListQueryData::fromArray($this->validated());
    }

    private function normalizeBooleanQuery(string $key): mixed
    {
        if (! $this->has($key)) {
            return null;
        }

        $value = $this->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $value;
    }
}
