<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

class Responses
{
    /**
     * @param Collection $response
     * @param SymfonyStyle $io
     *
     * @return int
     */
    public function handleError(Collection $response, SymfonyStyle $io): int
    {
        $error = $response->get('error');

        $io->error("{$error['code']}: {$error['message']}");

        return Command::FAILURE;
    }
}
