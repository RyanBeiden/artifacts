<?php

namespace App\Models;

use Carbon\Carbon;

class Achievement
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $description,
        public readonly int $points,
        public readonly string $type,
        public readonly string $target,
        public readonly int $total,
        public readonly array $rewards,
        public readonly int $current,
        public readonly ?Carbon $completed_at,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            code: $data['code'] ?? '',
            description: $data['description'] ?? '',
            points: $data['points'] ?? 0,
            type: $data['type'] ?? '',
            target: $data['target'] ?? '',
            total: $data['total'] ?? 0,
            rewards: $data['rewards'] ?? [],
            current: $data['current'] ?? 0,
            completed_at: !empty($data['completed_at']) ? Carbon::parse($data['completed_at']) : null,
        );
    }
}
