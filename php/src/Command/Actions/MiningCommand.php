<?php

namespace App\Command\Actions;

use App\Enums\Endpoints;
use App\Helpers\Cooldown;
use App\Helpers\IoBlocks;
use App\Helpers\Responses;
use App\Service\ApiService;
use App\Service\CharacterService;
use App\Service\ItemService;
use App\Service\MapService;
use App\Service\ResourceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'actions:mining', description: 'Mine resources with a selected character')]
class MiningCommand
{
    /**
     * @param ApiService $client
     */
    public function __construct(private ApiService $client) {}

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function __invoke(SymfonyStyle $io, InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = new QuestionHelper();
        $formatterHelper = new FormatterHelper();
        $cooldownHelper = new Cooldown();
        $responseHelper = new Responses();

        $characterService = new CharacterService($this->client);
        $mapService = new MapService($this->client);
        $resourceService = new ResourceService($this->client);
        $itemService = new ItemService();

        $characterNames = $characterService->characterNames($this->client);

        $formattedLine = $formatterHelper->formatSection(
            "Choose a character",
            "",
            "question"
        );

        $characterQuestion = new ChoiceQuestion($formattedLine, $characterNames, 0);
        $characterQuestion->setErrorMessage('Character name is invalid');

        $character = $questionHelper->ask($input, $output, $characterQuestion);

        $characterResponse = $this->client->get(Endpoints::Characters, [$character]);

        if ($characterResponse->has('error')) {
            return $responseHelper->handleError($characterResponse, $io);
        }

        $miningLevel = $characterResponse->get('mining_level', 1);
        $maxInventory = $characterResponse->get('inventory_max_items');
        $currentInventory = collect($characterResponse->get('inventory'))->sum('quantity');

        if ($currentInventory === $maxInventory) {
            $io->warning("Inventory full!");

            return Command::SUCCESS;
        }

        $io->comment(
            "Starting inventory: ({$currentInventory}/{$maxInventory})"
                . PHP_EOL
                . "Finding closest mining resource for <info>{$character}</info> (Mining Lvl {$miningLevel})"
        );

        $resourcesResponse = $resourceService->resources(
            $this->client,
            'mining',
            $miningLevel
        );

        if ($resourcesResponse->has('error')) {
            return $responseHelper->handleError($resourcesResponse, $io);
        }

        $resource = $resourcesResponse->sortByDesc('level')->first() ?? null;

        if (!$resource) {
            $io->warning("No gatherable resources found for mining skill at level {$miningLevel}");

            return Command::SUCCESS;
        }

        $maps = $mapService->maps($this->client, $resource['code'], 'resource');

        if ($maps->has('error')) {
            return $responseHelper->handleError($maps, $io);
        }

        $characterX = $characterResponse->get('x');
        $characterY = $characterResponse->get('y');

        $nearestMap = $characterService->mapNearestToCharacter(
            $maps,
            $characterX,
            $characterY
        );

        if (!$nearestMap) {
            $io->warning("No valid resource maps found for {$resource['name']}");

            return Command::SUCCESS;
        }

        if ($nearestMap['x'] !== $characterX || $nearestMap['y'] !== $characterY) {
            $io->comment("Moving character to ({$nearestMap['x']}, {$nearestMap['y']})");

            $moveResponse = $mapService->move(
                $this->client,
                $character,
                $nearestMap['x'],
                $nearestMap['y'],
                $nearestMap['map_id']
            );

            if ($moveResponse->has('error')) {
                return $responseHelper->handleError($moveResponse, $io);
            }

            if ($moveResponse->has('cooldown')) {
                $cooldown = $moveResponse->get('cooldown');

                $cooldownHelper->handleCooldown($cooldown, $io, $output);
            }
        }

        $items = collect();
        $xpGained = 0;

        while ($currentInventory < $maxInventory) {
            $gatherResponse = $this->client->gatherResource($character);

            if ($gatherResponse->has('error')) {
                return $responseHelper->handleError($gatherResponse, $io);
            }

            if ($gatherResponse->has('cooldown')) {
                $cooldown = $gatherResponse->get('cooldown');

                $cooldownHelper->handleCooldown($cooldown, $io, $output);
            }

            $details = $gatherResponse->get('details');

            // if ($gatherResponse->get('character')['mining_level'] > $miningLevel) {
            // @TODO: Check if user leveled up and consider checking highest level resource again.
            // }

            $items->push($details['items']);
            $xpGained += $details['xp'];

            $currentInventory = collect($gatherResponse->get('character')['inventory'])->sum('quantity');
        }

        $gatheredItems = $itemService->listItems($items);

        $gatheredItemsString = $gatheredItems
            ->map(fn($item) => "{$item['code']} ({$item['quantity']})")
            ->implode(', ');

        $io->success(
            "Total XP gained: {$xpGained}" . PHP_EOL . "Items gathered: " . $gatheredItemsString
        );

        // @TODO: Get nearest bank location first.

        $moveResponse = $mapService->move(
            $this->client,
            $character,
            4,
            1,
            334
        );

        if ($moveResponse->has('error')) {
            return $responseHelper->handleError($moveResponse, $io);
        }

        if ($moveResponse->has('cooldown')) {
            $cooldown = $moveResponse->get('cooldown');

            $cooldownHelper->handleCooldown($cooldown, $io, $output);
        }

        $io->comment("Depositing gathered items into the bank");

        $depositResponse = $this->client->depositItems($character, $gatheredItems->toArray());

        if ($depositResponse->has('error')) {
            return $responseHelper->handleError($depositResponse, $io);
        }

        if ($depositResponse->has('cooldown')) {
            $cooldown = $depositResponse->get('cooldown');

            $cooldownHelper->handleCooldown($cooldown, $io, $output);
        }

        $io->success("Deposit successful! This command can now be run again");

        return Command::SUCCESS;
    }
}
