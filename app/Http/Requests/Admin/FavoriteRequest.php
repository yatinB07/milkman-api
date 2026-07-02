<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\FavoriteData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
                Rule::unique('favorites', 'store_id')
                    ->where(fn ($query) => $query->where('customer_id', $this->input('customer_id'))),
            ],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
        ];
    }

    public function toData(): FavoriteData
    {
        return FavoriteData::fromArray($this->validated());
    }
}
