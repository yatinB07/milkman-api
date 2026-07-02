<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CashCollectionData;
use Illuminate\Foundation\Http\FormRequest;

class CashCollectionRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'message' => ['required', 'string'],
            'collected_at' => ['required', 'date'],
        ];
    }

    public function toData(): CashCollectionData
    {
        return CashCollectionData::fromArray($this->validated());
    }
}
