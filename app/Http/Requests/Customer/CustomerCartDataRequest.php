<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerCartDataQueryData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerCartDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function toData(): CustomerCartDataQueryData
    {
        return CustomerCartDataQueryData::fromArray($this->validated());
    }
}
