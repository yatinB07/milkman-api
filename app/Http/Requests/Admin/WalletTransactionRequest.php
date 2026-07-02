<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\WalletTransactionData;
use Illuminate\Foundation\Http\FormRequest;

class WalletTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:64'],
            'amount' => ['required', 'numeric', 'min:0'],
            'transacted_at' => ['required', 'date'],
        ];
    }

    public function toData(): WalletTransactionData
    {
        return WalletTransactionData::fromArray($this->validated());
    }
}
