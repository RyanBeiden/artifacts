<?php

namespace App\Models;

class Resource
{
    const DROP      = 'drop';
    const SKILL     = 'skill';
    const PAGE      = 'page';
    const SIZE      = 'size';
    const MAX_LEVEL = 'max_level';
    const MIN_LEVEL = 'min_level';

    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $skill,
        public readonly int $level,
        public readonly array $drops,
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
            skill: $data['skill'] ?? '',
            level: $data['level'] ?? 0,
            drops: $data['drops'] ?? [],
        );
    }
}
