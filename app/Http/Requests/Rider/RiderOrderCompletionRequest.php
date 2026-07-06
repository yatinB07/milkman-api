<?php

namespace App\Http\Requests\Rider;

use App\Data\Rider\RiderOrderCompletionData;
use Illuminate\Foundation\Http\FormRequest;

class RiderOrderCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'signature_image' => ['required', 'string'],
        ];
    }

    public function toData(): RiderOrderCompletionData
    {
        return RiderOrderCompletionData::fromArray($this->validated());
    }
}
