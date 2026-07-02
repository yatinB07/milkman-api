<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerNotificationRequest extends FormRequest
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
            'notified_at' => ['sometimes', 'required', 'date'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ];
    }

    public function toData(): CustomerNotificationData
    {
        return CustomerNotificationData::fromArray($this->validated());
    }
}
