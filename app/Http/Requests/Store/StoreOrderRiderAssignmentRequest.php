<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreOrderRiderAssignmentData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRiderAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'rider_id' => ['required', 'integer', Rule::exists('riders', 'id')->where('store_id', $this->user()?->getKey())],
        ];
    }

    public function toData(): StoreOrderRiderAssignmentData
    {
        return StoreOrderRiderAssignmentData::fromArray($this->validated());
    }
}
