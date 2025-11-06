<?php

namespace App\Models;

class Map
{
    // URL Parameter options
    const LAYER             = 'layer';
    const PAGE              = 'page';
    const SIZE              = 'size';
    const CONTENT_CODE      = 'content_code';
    const CONTENT_TYPE      = 'content_type';
    const HIDE_BLOCKED_MAPS = 'hide_blocked_maps';

    // POST body options
    const MAP_ID       = 'map_id';
    const X_COORDINATE = 'x';
    const Y_COORDINATE = 'y';

    public function __construct(
        public readonly int $map_id,
        public readonly string $name,
        public readonly string $skin,
        public readonly int $x,
        public readonly int $y,
        public readonly string $layer,
        public readonly array $access,
        public readonly array $interactions,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            map_id: $data['map_id'] ?? 0,
            name: $data['name'] ?? '',
            skin: $data['skin'] ?? '',
            x: $data['x'] ?? 0,
            y: $data['y'] ?? 0,
            layer: $data['layer'] ?? '',
            access: $data['access'] ?? [],
            interactions: $data['interactions'] ?? [],
        );
    }
}
