<?php

namespace App\Service;

use App\Enums\Endpoints;
use App\Enums\ResourceParams;
use Illuminate\Support\Collection;

class ResourceService
{
    /**
     * @param ApiService $client
     * @param string|null $skill
     * @param int|null $maxLevel
     *
     * @return Collection
     */
    public function resources(ApiService $client, ?string $skill = null, ?int $maxLevel = null): Collection
    {
        $params = [];

        if ($skill) {
            $params[] = [
                'name' => ResourceParams::Skill,
                'value' => $skill,
            ];
        }

        if ($maxLevel) {
            $params[] = [
                'name' => ResourceParams::MaxLevel,
                'value' => $maxLevel,
            ];
        }

        return $client->get(Endpoints::AllResources, [], $params);
    }
}
