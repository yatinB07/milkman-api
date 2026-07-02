<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\WalletTransactionData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'integer', 'exists:customers,id'],
            'message' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', 'string', 'max:64'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'transacted_at' => ['sometimes', 'required', 'date'],
        ];
    }

    public function toData(): WalletTransactionData
    {
        return WalletTransactionData::fromArray($this->validated());
    }
}
