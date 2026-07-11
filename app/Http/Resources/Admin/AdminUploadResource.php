<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin array{path: string, url: string} */
class AdminUploadResource extends JsonResource
{
    /** @return array{path: string, url: string} */
    public function toArray(Request $request): array
    {
        return [
            'path' => $this->resource['path'],
            'url' => $this->resource['url'],
        ];
    }
}
