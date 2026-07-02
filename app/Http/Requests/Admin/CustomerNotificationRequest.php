<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerNotificationRequest extends FormRequest
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
            'notified_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }

    public function toData(): CustomerNotificationData
    {
        return CustomerNotificationData::fromArray($this->validated());
    }
}
