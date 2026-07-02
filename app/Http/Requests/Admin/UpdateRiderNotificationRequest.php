<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\RiderNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRiderNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'rider_id' => ['sometimes', 'required', 'integer', 'exists:riders,id'],
            'notified_at' => ['sometimes', 'required', 'date'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string'],
        ];
    }

    public function toData(): RiderNotificationData
    {
        return RiderNotificationData::fromArray($this->validated());
    }
}
