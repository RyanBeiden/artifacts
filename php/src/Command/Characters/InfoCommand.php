<?php

namespace App\Command\Characters;

use App\Enums\Endpoints;
use App\Service\ApiService;
use App\Service\CharacterService;
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

        $myCharacters = $this->client->get(Endpoints::MyCharacters)
            ->pluck('name')
            ->toArray();

        $formattedLine = $formatterHelper->formatSection(
            "Choose a character",
            "",
            "question"
        );

        $characterQuestion = new ChoiceQuestion($formattedLine, $myCharacters, 0);
        $characterQuestion->setErrorMessage('Character name is invalid.');

        $character = $questionHelper->ask($input, $output, $characterQuestion);

        $response = $this->client->get(Endpoints::Characters, [$character]);

        if ($response->has('error')) {
            $error = $response->get('error');

            $io->error("{$error['code']}: {$error['message']}");

            return Command::FAILURE;
        };

        $name = $response->get('name', '');
        $level = $response->get('level', 0);
        
        $formattedLine = $formatterHelper->formatSection(
            "{$name}",
            "You are level {$level}"
        );

        $output->writeln($formattedLine);

        return Command::SUCCESS;
    }
}
