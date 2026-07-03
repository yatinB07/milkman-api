<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerOrderRatingData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'total_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_text' => ['required', 'string', 'max:1000'],
        ];
    }

    public function toData(): CustomerOrderRatingData
    {
        return CustomerOrderRatingData::fromArray($this->validated());
    }
}
