<?php

namespace App\Models;

class InventorySlot
{
    public function __construct(
        public readonly int $slot,
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
            slot: $data['slot'] ?? 0,
            code: $data['code'] ?? '',
            quantity: $data['quantity'] ?? 0,
        );
    }
}
