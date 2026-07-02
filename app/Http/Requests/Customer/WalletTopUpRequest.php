<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\WalletTopUpData;
use Illuminate\Foundation\Http\FormRequest;

class WalletTopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function toData(): WalletTopUpData
    {
        return WalletTopUpData::fromArray($this->validated());
    }
}
