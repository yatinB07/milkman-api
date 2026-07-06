<?php

namespace App\Http\Requests\Rider;

use App\Data\Rider\RiderOrderDecisionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RiderOrderDecisionRequest extends FormRequest
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

    public function toData(): RiderOrderDecisionData
    {
        return RiderOrderDecisionData::fromArray($this->validated());
    }
}
