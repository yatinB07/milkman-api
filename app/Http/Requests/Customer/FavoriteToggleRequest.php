<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\FavoriteToggleData;
use Illuminate\Foundation\Http\FormRequest;

class FavoriteToggleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ];
    }

    public function toData(): FavoriteToggleData
    {
        return FavoriteToggleData::fromArray($this->validated());
    }
}
