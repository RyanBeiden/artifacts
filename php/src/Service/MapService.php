<?php

namespace App\Service;

use App\Enums\MapParams;
use App\Enums\MoveParams;
use Illuminate\Support\Collection;

class MapService
{
    /**
     * @param ApiService $client
     * @param string|null $code
     * @param string|null $type
     *
     * @return Collection
     */
    public function maps(ApiService $client, ?string $code = null, ?string $type = null): Collection
    {
        $params = [];

        if ($code) {
            $params[] = [
                'name' => MapParams::ContentCode,
                'value' => $code,
            ];
        }

        if ($type) {
            $params[] = [
                'name' => MapParams::ContentType,
                'value' => $type,
            ];
        }

        return $client->getMaps($params);
    }

    /**
     * @param ApiService $client
     * @param string $character
     * @param int|null $x
     * @param int|null $y
     * @param int|null $mapId
     *
     * @return Collection
     */
    public function move(
        ApiService $client,
        string $character,
        ?int $x = null,
        ?int $y = null,
        ?int $mapId = null
    ): Collection {
        $params = [];

        if ($x) {
            $params[] = [
                'name' => MoveParams::X,
                'value' => $x,
            ];
        }

        if ($y) {
            $params[] = [
                'name' => MoveParams::Y,
                'value' => $y,
            ];
        }

        if ($mapId) {
            $params[] = [
                'name' => MoveParams::MapId,
                'value' => $mapId,
            ];
        }

        return $client->moveCharacter($character, $params);
    }
}
