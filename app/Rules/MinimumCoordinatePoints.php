<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumCoordinatePoints implements ValidationRule
{
    public function __construct(private readonly int $minimumPoints)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if ($this->countCoordinatePoints($value) < $this->minimumPoints) {
            $fail(__('validation.zone_coordinates_min_points', [
                'min' => $this->minimumPoints,
            ]));
        }
    }

    private function countCoordinatePoints(string $coordinates): int
    {
        preg_match_all(
            '/-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?|-?\d+(?:\.\d+)?\s+-?\d+(?:\.\d+)?/',
            $coordinates,
            $matches,
        );

        return count($matches[0]);
    }
}
