<?php

namespace App\Service;

use Illuminate\Support\Collection;

class ItemService
{
    /**
     * @param Collection $items
     *
     * @return Collection
     */
    public function listItems(Collection $items): Collection
    {
        return $items
            ->collapse()
            ->groupBy('code')
            ->map(function ($group, $code) {
                return [
                    'code' => $code,
                    'quantity' => $group->sum('quantity'),
                ];
            })
            ->values();
    }
}
