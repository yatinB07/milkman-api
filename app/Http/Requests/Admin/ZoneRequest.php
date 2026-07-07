<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\ZoneData;
use App\Rules\MinimumCoordinatePoints;
use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
{
    private const MINIMUM_COORDINATE_POINTS = 3;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status') && ! $this->has('is_active')) {
            $this->merge([
                'is_active' => $this->input('status'),
            ]);
        }
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'coordinates' => ['required', 'string', new MinimumCoordinatePoints(self::MINIMUM_COORDINATE_POINTS)],
            'alias' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): ZoneData
    {
        return ZoneData::fromArray($this->validated());
    }
}
