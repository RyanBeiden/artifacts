<?php

namespace App\Models;

use Carbon\Carbon;

class Status
{
    public function __construct(
        public readonly string $version,
        public readonly Carbon $server_time,
        public readonly int $max_level,
        public readonly int $max_skill_level,
        public readonly int $characters_online,
        public readonly array $season,
        public readonly array $rate_limits,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'] ?? '',
            server_time: Carbon::parse($data['server_time'] ?? Carbon::now()),
            max_level: $data['max_level'] ?? 0,
            max_skill_level: $data['max_skill_level'] ?? 0,
            characters_online: $data['characters_online'] ?? 0,
            season: $data['season'] ?? [],
            rate_limits: $data['rate_limits'] ?? [],
        );
    }
}
