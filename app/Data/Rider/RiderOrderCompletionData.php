<?php

namespace App\Data\Rider;

final readonly class RiderOrderCompletionData
{
    public function __construct(public string $signatureImage) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(signatureImage: (string) $data['signature_image']);
    }
}
