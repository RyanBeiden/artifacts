<?php

namespace App\Models;

use Carbon\Carbon;

class Cooldown
{
    public function __construct(
        public readonly int $total_seconds,
        public readonly int $remaining_seconds,
        public readonly Carbon $started_at,
        public readonly Carbon $expiration,
        public readonly string $reason,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total_seconds: $data['total_seconds'] ?? 0,
            remaining_seconds: $data['remaining_seconds'] ?? 0,
            started_at: Carbon::parse($data['started_at'] ?? Carbon::now()),
            expiration: Carbon::parse($data['expiration'] ?? Carbon::now()),
            reason: $data['reason'] ?? '',
        );
    }

    /**
     * @return string
     */
    public function displayReason(): string
    {
        return ucwords(str_replace('_', ' ', $this->reason));
    }
}
