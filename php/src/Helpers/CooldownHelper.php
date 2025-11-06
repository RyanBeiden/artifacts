<?php

namespace App\Helpers;

use App\Models\Cooldown;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CooldownHelper
{
    /**
     * @param SymfonyStyle|null $io
     * @param OutputInterface|null $output
     */
    public function __construct(
        private ?SymfonyStyle $io = null,
        private ?OutputInterface $output = null
    ) {}

    /**
     * @param Cooldown $cooldown
     *
     * @return void
     */
    public function handleCooldown(Cooldown $cooldown): void
    {
        $expiration = $cooldown->expiration;
        $reason = $cooldown->displayReason();
        $remainingSeconds = $cooldown->started_at->diffInSeconds($expiration);

        $sleepUntil = $expiration->timestamp + ($expiration->micro / 1_000_000);
        $now = microtime(true);
        $start = microtime(true);

        $isCommand = ($this->io && $this->output);

        if ($isCommand) {
            $progressBar = new ProgressBar($this->output, $remainingSeconds);

            $progressBar->setFormat(
                "<info>{$reason}...</info> [%bar%] <comment>%remaining:-6s% remaining</comment>"
            );

            $progressBar->start();
        }

        while ($sleepUntil > $now) {
            usleep(500_000); // Sleep for 0.5 seconds
            $now = microtime(true);
            $elapsed = (int)($now - $start);

            if ($isCommand) {
                $progressBar->setProgress(min($elapsed, $remainingSeconds));
            }
        }

        if ($isCommand) {
            $progressBar->finish();
            $this->io->newLine(2);
        }
    }
}
