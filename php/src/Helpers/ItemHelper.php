<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ItemHelper
{
    /**
     * @param Collection $items
     *
     * @return Collection
     */
    public function groupTotalsByCode(Collection $items): Collection
    {
        return $items
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
