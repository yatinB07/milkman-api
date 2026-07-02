<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\PayoutRequestData;
use App\Enums\PayoutStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PayoutRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string|Enum>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', new Enum(PayoutStatus::class)],
            'proof_path' => ['nullable', 'string', 'max:2048'],
            'requested_at' => ['required', 'date'],
            'request_type' => ['required', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:50'],
            'upi_id' => ['nullable', 'string', 'max:255'],
            'paypal_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): PayoutRequestData
    {
        return PayoutRequestData::fromArray($this->validated());
    }
}
