<?php

namespace App\Helpers;

use Symfony\Component\Console\Helper\ProgressBar;
use Carbon\Carbon;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Cooldown
{
    /**
     * @param array $cooldown
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     *
     * @return void
     */
    public function handleCooldown(array $cooldown, SymfonyStyle $io, OutputInterface $output): void
    {
        $expiration = Carbon::parse($cooldown['expiration']);
        $reason = ucwords(str_replace('_', ' ', $cooldown['reason']));
        $remainingSeconds = $cooldown['remaining_seconds'];

        $sleepUntil = $expiration->timestamp + ($expiration->micro / 1_000_000);
        $now = microtime(true);
        $start = microtime(true);

        $progressBar = new ProgressBar($output, $remainingSeconds);

        $progressBar->setFormat(
            "<info>{$reason}...</info> [%bar%] <comment>%remaining:-6s% remaining</comment>"
        );

        $progressBar->start();

        while ($sleepUntil > $now) {
            usleep(500_000); // Sleep for 0.5 seconds
            $now = microtime(true);
            $elapsed = (int)($now - $start);
            $progressBar->setProgress(min($elapsed, $remainingSeconds));
        }

        $progressBar->finish();

        $io->newLine(2);
    }
}
