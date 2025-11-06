<?php

namespace App\Command\Actions;

use App\Exceptions\ApiException;
use App\Exceptions\ConditionsNotMetException;
use App\Helpers\CooldownHelper;
use App\Helpers\DropHelper;
use App\Helpers\IoBlocks;
use App\Models\Map;
use App\Models\Resource;
use App\Service\ApiService;
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
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function __invoke(SymfonyStyle $io, InputInterface $input, OutputInterface $output): int
    {
        /* ---------------------- Setup ---------------------- */

        $questionHelper  = new QuestionHelper();
        $formatterHelper = new FormatterHelper();
        $cooldownHelper  = new CooldownHelper($io, $output);
        $dropHelper      = new DropHelper();

        /* --------------------------------------------------- */

        try {
            $myCharacters   = $this->client->getMyCharacters();
            $characterNames = $myCharacters->pluck('name')->toArray();
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $formattedLine     = $formatterHelper->formatSection("Choose a character", "", "question");
        $characterQuestion = new ChoiceQuestion($formattedLine, $characterNames, 0);
        $characterQuestion->setErrorMessage('Character does not exist');

        $characterName = $questionHelper->ask($input, $output, $characterQuestion);

        try {
            $character = $this->client->getCharacter($characterName);
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $currentInventoryCount = $character->currentInventoryCount();
        $inventoryMaxItems     = $character->inventory_max_items;
        $miningLevel           = $character->mining_level;

        if ($currentInventoryCount === $inventoryMaxItems) {
            $io->warning("Inventory full!");

            return Command::SUCCESS;
        }

        $io->comment(
            "Starting inventory: ({$currentInventoryCount}/{$inventoryMaxItems})"
                . PHP_EOL
                . "Finding closest mining resource for <info>{$characterName}</info> (Mining Lvl {$miningLevel})"
        );

        try {
            $resources = $this->client->getAllResources([
                Resource::SKILL     => 'mining',
                Resource::MAX_LEVEL => $miningLevel,
            ]);
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $resource = $resources->sortByDesc('level')->first() ?? null;

        if (!$resource) {
            $io->warning("No gatherable resources found for mining skill at level {$miningLevel}");

            return Command::SUCCESS;
        }

        try {
            $maps = $this->client->getMaps(
                [
                    Map::CONTENT_CODE => $resource->code,
                    Map::CONTENT_TYPE => 'resource',
                ],
                $character
            );
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($maps->isEmpty()) {
            $io->warning("No valid resource maps found for {$resource->name}");

            return Command::SUCCESS;
        }

        foreach ($maps as $map) {
            if ($map->map_id === $character->map_id) {
                break;
            }

            try {
                $io->comment("Moving character to ({$map->x}, {$map->y})");

                [$character, $cooldown] = $this->client->moveCharacter(
                    $character,
                    $map->map_id
                );

                $cooldownHelper->handleCooldown($cooldown);
            } catch (ConditionsNotMetException $e) {
                continue;
            } catch (ApiException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        $items = collect();
        $xpGained = 0;

        while ($currentInventoryCount < $inventoryMaxItems) {
            try {
                [$character, $cooldown, $details] = $this->client->gatherResource($character);
            } catch (ApiException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }

            $cooldownHelper->handleCooldown($cooldown);

            $items = $items->merge($details->items);
            $xpGained += $details->xp;

            $currentInventoryCount = $character->currentInventoryCount();
        }

        $groupedDrops = $dropHelper->groupTotalsByCode($items);

        $gatheredItemsString = $groupedDrops
            ->map(fn($item) => "{$item['code']} ({$item['quantity']})")
            ->implode(', ');

        $io->success(
            "Total XP gained: {$xpGained}" . PHP_EOL . "Items gathered: " . $gatheredItemsString
        );

        if ($items->isEmpty()) {
            IoBlocks::info("No items to deposit", $io);

            return Command::SUCCESS;
        }

        try {
            $maps = $this->client->getMaps(
                [Map::CONTENT_TYPE => 'bank'],
                $character
            );
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($maps->isEmpty()) {
            $io->warning("No valid maps found for a bank");

            return Command::SUCCESS;
        }

        foreach ($maps as $map) {
            if ($map->map_id === $character->map_id) {
                break;
            }

            try {
                $io->comment("Moving character to ({$map->x}, {$map->y})");

                [$character, $cooldown] = $this->client->moveCharacter(
                    $character,
                    $map->map_id
                );

                $cooldownHelper->handleCooldown($cooldown);
            } catch (ConditionsNotMetException $e) {
                continue;
            } catch (ApiException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        $io->comment("Depositing gathered items into the bank");

        try {
            [$character, $cooldown] = $this->client->depositItems($characterName, $groupedDrops);

            $cooldownHelper->handleCooldown($cooldown);
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success("Deposit successful!");

        return Command::SUCCESS;
    }
}
