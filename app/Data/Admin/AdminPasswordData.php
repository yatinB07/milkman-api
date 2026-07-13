<?php

namespace App\Data\Admin;

final readonly class AdminPasswordData
{
    private function __construct(public string $currentPassword, public string $password) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: (string) $data['current_password'],
            password: (string) $data['password'],
        );
    }
}
