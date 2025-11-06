<?php

namespace App\Models;

use Illuminate\Support\Collection;

class SkillInfo
{
    public function __construct(
        public readonly int $xp,
        public readonly Collection $items,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            xp: $data['xp'] ?? 0,
            items: collect($data['items'] ?? [])
                ->map(fn($item) => Drop::fromArray($item)),
        );
    }
}
