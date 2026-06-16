<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdentityProfileResource extends JsonResource
{
    public function __construct($resource, private readonly string $identityType)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->identityType,
            'name' => $this->displayName(),
            'email' => $this->resource->email,
            'roles' => $this->resource->getRoleNames()->values()->all(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }

    private function displayName(): string
    {
        return $this->resource->title ?? $this->resource->name;
    }
}
