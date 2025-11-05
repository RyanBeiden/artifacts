<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Enums\Endpoints;
use App\Enums\ItemParams;
use App\Enums\ResourceParams;
use App\Enums\MapParams;
use App\Enums\MoveParams;
use Exception;
use Illuminate\Support\Collection;

class ApiService
{
    const NOT_FOUND = 404;

    private HttpClientInterface $apiClient;

    public function __construct(HttpClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param Endpoints $endpoint
     * @param array $arguments
     * @param array $params
     *
     * @return Collection
     */
    public function get(Endpoints $endpoint, array $arguments = [], array $params = []): Collection
    {
        $url = $this->buildUrl($endpoint, $arguments, $params);

        $response = $this->apiClient->request('GET', $url);

        try {
            $data = $response->toArray()['data'];
        } catch (Exception $e) {
            $content = $response->getContent(false);

            $data = json_decode($content, true);
        }

        return collect($data);
    }

    /**
     * @param Endpoints $endpoint
     * @param array $arguments
     * @param array $body
     *
     * @return Collection
     */
    public function post(Endpoints $endpoint, array $arguments = [], array $body = []): Collection
    {
        $url = $this->buildUrl($endpoint, $arguments, []);

        $response = $this->apiClient->request('POST', $url, ['json' => $body]);

        try {
            $data = $response->toArray()['data'];
        } catch (Exception $e) {
            $content = $response->getContent(false);

            $data = json_decode($content, true);
        }

        return collect($data);
    }

    /**
     * @param array $params
     *
     * @return Collection
     */
    public function getAllResources(array $params = []): Collection
    {
        $validParams = $this->areParamsValid($params, ResourceParams::class);

        if (!$validParams) {
            return collect();
        }

        return $this->get(Endpoints::AllResources, [], $params);
    }

    /**
     * @param array $params
     *
     * @return Collection
     */
    public function getMaps(array $params = []): Collection
    {
        $validParams = $this->areParamsValid($params, MapParams::class);

        if (!$validParams) {
            return collect();
        }

        return $this->get(Endpoints::AllMaps, [], $params);
    }

    /**
     * @param string $character
     * @param array $params
     *
     * @return Collection
     */
    public function moveCharacter(string $character, array $params = []): Collection
    {
        $validParams = $this->areParamsValid($params, MoveParams::class);

        if (!$validParams) {
            return collect();
        }

        $body = [];

        foreach ($params as $param) {
            $body[$param['name']->value] = $param['value'];
        }

        return $this->post(Endpoints::Move, ['name' => $character], $body);
    }

    /**
     * @param string $character
     *
     * @return Collection
     */
    public function gatherResource(string $character): Collection
    {
        return $this->post(Endpoints::Gathering, ['name' => $character], []);
    }

    /**
     * @param string $character
     * @param array $items
     *
     * @return Collection
     */
    public function depositItems(string $character, array $items): Collection
    {
        return $this->post(Endpoints::DepositItem, ['name' => $character], $items);
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

            foreach ($params as $param) {
                $url .= "{$param['name']->value}={$param['value']}";

                if (end($params) !== $param) {
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

    /**
     * @param array $params
     * @param string $enumClass
     *
     * @return bool
     */
    private function areParamsValid(array $params, string $enumClass): bool
    {
        foreach ($params as $param) {
            if (!$param['name'] || !($param['name'] instanceof $enumClass)) {
                throw new Exception("Invalid parameter type. Expected instance of {$enumClass} enum.");

                return false;
            }

            if (!isset($param['value'])) {
                throw new Exception("Invalid parameter type. Expected value for {$param['name']}.");

                return false;
            }
        }

        return true;
    }
}
