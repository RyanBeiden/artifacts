<?php

namespace App\Models;

class Drop
{
    public function __construct(
        public readonly string $code,
        public readonly int $quantity,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            quantity: $data['quantity'] ?? 0,
        );
    }
}
