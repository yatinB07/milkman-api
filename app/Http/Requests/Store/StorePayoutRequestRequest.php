<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StorePayoutRequestData;
use Illuminate\Foundation\Http\FormRequest;

class StorePayoutRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'request_type' => ['required', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:50'],
            'upi_id' => ['nullable', 'string', 'max:255'],
            'paypal_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): StorePayoutRequestData
    {
        return StorePayoutRequestData::fromArray($this->validated());
    }
}
