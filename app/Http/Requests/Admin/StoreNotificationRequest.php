<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'notified_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }

    public function toData(): StoreNotificationData
    {
        return StoreNotificationData::fromArray($this->validated());
    }
}
