<?php

namespace App\Http\Requests\Store;

use App\Data\Store\StoreOrderDecisionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', Rule::in(['accepted', 'rejected'])],
            'rejection_comment' => ['required_if:decision,rejected', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): StoreOrderDecisionData
    {
        return StoreOrderDecisionData::fromArray($this->validated());
    }
}
