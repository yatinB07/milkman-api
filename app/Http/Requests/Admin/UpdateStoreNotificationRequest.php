<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreNotificationData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'required', 'integer', 'exists:stores,id'],
            'notified_at' => ['sometimes', 'required', 'date'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ];
    }

    public function toData(): StoreNotificationData
    {
        return StoreNotificationData::fromArray($this->validated());
    }
}
