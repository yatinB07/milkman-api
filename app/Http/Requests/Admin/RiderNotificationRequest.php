<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\RiderNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class RiderNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'rider_id' => ['required', 'integer', 'exists:riders,id'],
            'notified_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ];
    }

    public function toData(): RiderNotificationData
    {
        return RiderNotificationData::fromArray($this->validated());
    }
}
