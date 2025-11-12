<?php

namespace App\Command\Actions;

use App\Exceptions\ApiException;
use App\Exceptions\ConditionsNotMetException;
use App\Helpers\CooldownHelper;
use App\Helpers\IoBlocks;
use App\Helpers\ItemHelper;
use App\Models\Map;
use App\Models\Resource;
use App\Service\ApiService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'levelup:gathering',
    description: 'Gather resources to gain XP for a selected character'
)]
class LevelUpGatheringCommand
{
    /**
     * @param ApiService $client
     */
    public function __construct(private ApiService $client) {}

    /**
     * @param int $gatherUntilLevel
     * @param int $item
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function __invoke(
        #[Argument('The level to gather resources until.')] int $gatherUntilLevel,
        SymfonyStyle $io,
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $serverDetails = $this->client->getServerDetails();
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($gatherUntilLevel > $serverDetails->max_skill_level) {
            $io->error("The maximum skill level is {$serverDetails->max_skill_level}");

            return Command::FAILURE;
        }

        /* ---------------------- Setup ---------------------- */

        $questionHelper  = new QuestionHelper();
        $formatterHelper = new FormatterHelper();
        $cooldownHelper  = new CooldownHelper($io, $output);
        $itemHelper      = new ItemHelper();

        /* --------------------------------------------------- */

        try {
            $myCharacters   = $this->client->getMyCharacters();
            $characterNames = $myCharacters->pluck('name')->toArray();
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $formattedLine = $formatterHelper->formatSection("Choose a character", "", "question");
        $question      = new ChoiceQuestion($formattedLine, $characterNames, 0);
        $question->setErrorMessage('Character does not exist');

        $characterName = $questionHelper->ask($input, $output, $question);

        try {
            $character = $this->client->getCharacter($characterName);
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        try {
            $resources = $this->client->getAllResources();
        } catch (ApiException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $skills = $resources->unique('skill')->pluck('skill')->toArray();

        $formattedLine = $formatterHelper->formatSection(
            "What resource do you want to gather?",
            "",
            "question"
        );

        $question = new ChoiceQuestion($formattedLine, $skills, 0);
        $question->setErrorMessage('Skill does not exist');

        $skill = $questionHelper->ask($input, $output, $question);

        $skillLevel = $character->{$skill . '_level'};

        if ($gatherUntilLevel <= $skillLevel) {
            $io->warning("{$characterName} is already at or past {$skill} level {$gatherUntilLevel}");

            return Command::SUCCESS;
        }

        $currentInventoryCount = $character->currentInventoryCount();
        $inventoryMaxItems     = $character->inventory_max_items;

        while ($skillLevel < $gatherUntilLevel) {
            if ($currentInventoryCount === $inventoryMaxItems) {
                $io->warning("Inventory full!");

                return Command::SUCCESS;
            }

            $io->comment(
                "Starting inventory: ({$currentInventoryCount}/{$inventoryMaxItems})"
                    . PHP_EOL
                    . "Finding closest {$skill} resource for <info>{$characterName}</info>"
                    . " (" . ucfirst($skill) . " Lvl {$skillLevel})"
            );

            $resourcesForSkill = $resources
                ->filter(function (Resource $resource) use ($skillLevel, $skill) {
                    return $resource->skill === $skill && $resource->level <= $skillLevel;
                })
                ->sortByDesc('level');

            if ($resourcesForSkill->isEmpty()) {
                $io->warning("No gatherable resources found for {$skill} at level {$skillLevel}");

                return Command::SUCCESS;
            }

            $resource = $resourcesForSkill->first();

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

                break;
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

                // if ($character->{$skill}}_level > $skillLevel) {
                // @TODO: Check if user leveled up and consider checking highest level resource again.
                // }

                $items = $items->merge($details->items);
                $xpGained += $details->xp;

                $currentInventoryCount = $character->currentInventoryCount();
                $skillLevel = $character->{$skill . '_level'};

                if ($skillLevel >= $gatherUntilLevel) {
                    break;
                }
            }

            $items = $itemHelper->groupTotalsByCode($items);

            if ($items->isEmpty()) {
                IoBlocks::info("No items to deposit", $io);

                // @TODO: Continue loop
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

                break;
            }

            $io->comment("Depositing gathered items into the bank");

            try {
                [$character, $cooldown] = $this->client->depositItems($character, $items);

                $cooldownHelper->handleCooldown($cooldown);
            } catch (ApiException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }

            $currentInventoryCount = $character->currentInventoryCount();
            $inventoryMaxItems     = $character->inventory_max_items;
            $skillLevel            = $character->{$skill . '_level'};

            sleep(5);
        }

        $io->success("Your {$skill} skill has reached level {$gatherUntilLevel}!");

        return Command::SUCCESS;
    }
}
