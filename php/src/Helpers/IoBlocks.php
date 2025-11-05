<?php

namespace App\Helpers;

use Symfony\Component\Console\Style\SymfonyStyle;

class IoBlocks
{
    /**
     * @param string $message
     * @param SymfonyStyle $io
     *
     * @return void
     */
    public static function info(string $message, SymfonyStyle $io): void
    {
        $io->block($message, null, 'info');
    }

    /**
     * @param string $message
     * @param SymfonyStyle $io
     *
     * @return void
     */
    public static function note(string $message, SymfonyStyle $io): void
    {
        $io->block($message, 'NOTE', 'fg=yellow');
    }
}
