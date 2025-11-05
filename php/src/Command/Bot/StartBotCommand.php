<?php

namespace App\Command\Bot;

use App\Service\DiscordService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'bot:start')]
class StartBotCommand
{
    /**
     * @param DiscordService $discord
     */
    public function __construct(private DiscordService $discord) {}

    /**
     * @return int
     */
    public function __invoke(): int
    {
        $this->discord->run();

        return Command::SUCCESS;
    }
}
