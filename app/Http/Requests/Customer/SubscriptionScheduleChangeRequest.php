<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\SubscriptionScheduleChangeData;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionScheduleChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'distinct'],
        ];
    }

    public function toData(): SubscriptionScheduleChangeData
    {
        return SubscriptionScheduleChangeData::fromArray($this->validated());
    }
}
