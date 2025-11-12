<?php

namespace App\Command\Characters;

use App\Exceptions\ApiException;
use App\Service\ApiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'characters:info')]
class InfoCommand
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

        $formattedLine = $formatterHelper->formatSection(
            "{$character->name}",
            "Level {$character->level}"
        );

        $output->writeln($formattedLine);

        return Command::SUCCESS;
    }
}
