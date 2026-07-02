<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CashCollectionData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCashCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'required', 'integer', 'exists:stores,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'message' => ['sometimes', 'required', 'string'],
            'collected_at' => ['sometimes', 'required', 'date'],
        ];
    }

    public function toData(): CashCollectionData
    {
        return CashCollectionData::fromArray($this->validated());
    }
}
