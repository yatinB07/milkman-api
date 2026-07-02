<?php

namespace App\Data\Customer;

final readonly class WalletTopUpData
{
    public function __construct(public string $amount) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((string) $data['amount']);
    }
}
