<?php

namespace App\Http\Requests\Rider;

use App\Data\Rider\RiderSubscriptionDeliveryDateData;
use Illuminate\Foundation\Http\FormRequest;

class RiderSubscriptionDeliveryDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'selected_date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function toData(): RiderSubscriptionDeliveryDateData
    {
        return RiderSubscriptionDeliveryDateData::fromArray($this->validated());
    }
}
