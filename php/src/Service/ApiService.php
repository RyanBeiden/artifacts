<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Enums\Endpoints;
use App\Enums\ErrorCodes;
use App\Exceptions\ApiException;
use App\Exceptions\ConditionsNotMetException;
use App\Models\Account;
use App\Models\Achievement;
use App\Models\Character;
use App\Models\Cooldown;
use App\Models\Map;
use App\Models\MyAccountDetail;
use App\Models\Resource;
use App\Models\SkillInfo;
use App\Models\Status;
use Exception;
use Illuminate\Support\Collection;

class ApiService
{
    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $apiClient;

    /**
     * @param HttpClientInterface $apiClient
     */
    public function __construct(HttpClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param Endpoints $endpoint
     * @param array $arguments
     * @param array $params
     * @param array|null $accumulator
     *
     * @throws ApiException
     * @return array
     */
    public function get(
        Endpoints $endpoint,
        array $arguments = [],
        array $params = [],
        ?array &$accumulator = null
    ): array {
        $url = $this->buildUrl($endpoint, $arguments, $params);

        $response = $this->apiClient->request('GET', $url);

        try {
            $data = $response->toArray();
        } catch (Exception $e) {
            $content = $response->getContent(false);

            $data = json_decode($content, true);
        }

        if (isset($data['error'])) {
            $errorMessage = $data['error']['message'] ?? 'An error occurred';
            $errorCode = $data['error']['code'] ?? 0;

            $message = "{$errorCode}: {$errorMessage}";

            throw new ApiException($message, $errorCode);
        }

        if (!isset($data['page']) || !isset($data['pages'])) {
            return $data;
        }

        if ($accumulator === null) {
            $accumulator = [];
        }

        $currentPage = $data['page'];
        $totalPages  = $data['pages'];

        $filteredData = array_diff_key(
            $data,
            array_flip(['page', 'pages', 'total', 'size'])
        );

        $accumulator = array_merge_recursive($accumulator, $filteredData);

        if ($currentPage < $totalPages) {
            $params['page'] = $currentPage + 1;

            return $this->get(
                $endpoint,
                $arguments,
                $params,
                $accumulator
            );
        }

        return $accumulator;
    }

    /**
     * @param Endpoints $endpoint
     * @param array $arguments
     * @param array $body
     *
     * @return array
     */
    public function post(Endpoints $endpoint, array $arguments = [], array $body = []): array
    {
        $url = $this->buildUrl($endpoint, $arguments, []);

        $response = $this->apiClient->request('POST', $url, ['json' => $body]);

        try {
            $data = $response->toArray();
        } catch (Exception $e) {
            $content = $response->getContent(false);

            $data = json_decode($content, true);
        }

        if (isset($data['error'])) {
            $errorMessage = $data['error']['message'] ?? 'An error occurred';
            $errorCode = $data['error']['code'] ?? 0;

            $message = "{$errorCode}: {$errorMessage}";

            if ($errorCode === ErrorCodes::ConditionsNotMet) {
                throw new ConditionsNotMetException($message, $errorCode);
            }

            throw new ApiException($message, $errorCode);
        }

        return $data;
    }

    /**
     * @return Status
     */
    public function getServerDetails(): Status
    {
        $response = $this->get(Endpoints::ServerDetails);

        return Status::fromArray($response['data']);
    }

    /**
     * @return MyAccountDetail
     */
    public function getMyDetails(): MyAccountDetail
    {
        $response = $this->get(Endpoints::MyDetails);

        return MyAccountDetail::fromArray($response['data']);
    }

    /**
     * @param MyAccountDetail $myAccountDetail
     *
     * @return Collection<Achievement>
     */
    public function getAccountAchievements(MyAccountDetail $myAccountDetail): Collection
    {
        $response = $this->get(
            Endpoints::AccountAchievements,
            [Account::ACCOUNT => $myAccountDetail->username],
        );

        $achievements = collect();

        foreach ($response['data'] as $achievement) {
            $achievements->push(Achievement::fromArray($achievement));
        }

        return $achievements;
    }

    /**
     * @return Collection<Character>
     */
    public function getMyCharacters(): Collection
    {
        $response = $this->get(Endpoints::MyCharacters);

        $characters = collect();

        foreach ($response['data'] as $character) {
            $characters->push(Character::fromArray($character));
        }

        return $characters;
    }

    /**
     * @param string $characterName
     *
     * @return Character
     */
    public function getCharacter(string $characterName): Character
    {
        $response = $this->get(Endpoints::Characters, [$characterName]);

        return Character::fromArray($response['data']);
    }

    /**
     * @param array $params
     *
     * @return Collection<Resource>
     */
    public function getAllResources(array $params = []): Collection
    {
        $response = $this->get(Endpoints::AllResources, [], $params);

        $resources = collect();

        foreach ($response['data'] as $resource) {
            $resources->push(Resource::fromArray($resource));
        }

        return $resources;
    }

    /**
     * @param array $params
     * @param Character|null $character
     *
     * @return Collection<Map>
     */
    public function getMaps(array $params = [], ?Character $character = null): Collection
    {
        $response = $this->get(Endpoints::AllMaps, [], $params);

        $maps = collect();

        foreach ($response['data'] as $map) {
            $maps->push(Map::fromArray($map));
        }

        if ($character) {
            // Sort maps by nearest to character
            return $maps
                ->sortBy(function (Map $map) use ($character) {
                    return sqrt(
                        pow($character->x - $map->x, 2) + pow($character->y - $map->y, 2)
                    );
                })
                ->values();
        }

        return $maps;
    }

    /**
     * @param Character $character
     * @param int $mapId
     *
     * @return array
     */
    public function moveCharacter(Character $character, int $mapId): array
    {
        $response = $this->post(
            Endpoints::Move,
            [Character::CHARACTER_NAME => $character->name],
            [Map::MAP_ID => $mapId]
        );

        $character = Character::fromArray($response['data']['character']);
        $cooldown  = Cooldown::fromArray($response['data']['cooldown']);

        return [$character, $cooldown];
    }

    /**
     * @param Character $character
     *
     * @return array
     */
    public function gatherResource(Character $character): array
    {
        $response = $this->post(
            Endpoints::Gathering,
            [Character::CHARACTER_NAME => $character->name],
            []
        );

        $character = Character::fromArray($response['data']['character']);
        $cooldown  = Cooldown::fromArray($response['data']['cooldown']);
        $details   = SkillInfo::fromArray($response['data']['details']);

        return [$character, $cooldown, $details];
    }

    /**
     * @param Character $character
     * @param Collection $items
     *
     * @return array
     */
    public function depositItems(Character $character, Collection $items): array
    {
        $response = $this->post(
            Endpoints::DepositItem,
            [Character::CHARACTER_NAME => $character->name],
            $items->toArray()
        );

        $character = Character::fromArray($response['data']['character']);
        $cooldown  = Cooldown::fromArray($response['data']['cooldown']);

        return [$character, $cooldown];
    }

    /**
     * @param Endpoints $endpoint
     * @param array $arguments
     * @param array $params
     *
     * @return string
     */
    private function buildUrl(Endpoints $endpoint, array $arguments, array $params): string
    {
        $url = $endpoint->value;

        if ($arguments) {
            $argumentNames = array_keys($arguments);
            $placeholder = $this->extractPlaceholderName($url);

            if ($argumentNames && $placeholder && in_array($placeholder, $argumentNames)) {
                $url = str_replace("{" . $placeholder . "}", $arguments[$placeholder], $url);
                unset($arguments[$placeholder]);
            }

            if ($arguments) {
                $argumentString = implode('/', array_values($arguments));
                $url = "{$url}/{$argumentString}";
            }
        }

        if ($params) {
            $url = "{$url}?";

            foreach ($params as $name => $value) {
                $url .= "{$name}={$value}";

                if (end($params) !== $value) {
                    $url .= "&";
                }
            }
        }

        return $url;
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function extractPlaceholderName(string $url): ?string
    {
        if (preg_match('/\{([^}]+)\}/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
