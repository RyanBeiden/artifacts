<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class DropHelper
{
    /**
     * @param Collection $details
     *
     * @return Collection
     */
    public function groupTotalsByCode(Collection $details): Collection
    {
        return $details
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
