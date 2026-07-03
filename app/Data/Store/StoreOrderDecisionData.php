<?php

namespace App\Data\Store;

final readonly class StoreOrderDecisionData
{
    public function __construct(
        public string $decision,
        public ?string $rejectionComment,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $comment = $data['rejection_comment'] ?? null;

        return new self(
            decision: (string) $data['decision'],
            rejectionComment: is_string($comment) && $comment !== '' ? $comment : null,
        );
    }
}
