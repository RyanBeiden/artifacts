<?php

namespace App\Service;

use App\Enums\Endpoints;
use Illuminate\Support\Collection;

class CharacterService
{
    /**
     * @param ApiService $client
     *
     * @return array
     */
    public function characterNames(ApiService $client): array
    {
        return $client->get(Endpoints::MyCharacters)
            ->pluck('name')
            ->toArray();
    }

    /**
     * @param Collection $maps
     * @param int $characterX
     * @param int $characterY
     *
     * @return array|null
     */
    public function mapNearestToCharacter(
        Collection $maps,
        int $characterX,
        int $characterY
    ): ?array {
        $nearestMap = null;
        $nearestDistance = null;

        $maps->each(function ($map) use ($characterX, $characterY, &$nearestDistance, &$nearestMap) {
            $mapX = $map['x'] ?? null;
            $mapY = $map['y'] ?? null;

            if ($mapX === null || $mapY === null) {
                return false;
            }

            $distance = sqrt(pow($characterX - $mapX, 2) + pow($characterY - $mapY, 2));

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestMap = $map;
            }
        });

        return $nearestMap;
    }
}
