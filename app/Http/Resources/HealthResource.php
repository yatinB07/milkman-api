<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{name: string, status: string, version: string} $resource
 */
class HealthResource extends JsonResource
{
    /**
     * @return array{name: string, status: string, version: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'],
            'status' => $this->resource['status'],
            'version' => $this->resource['version'],
        ];
    }
}
