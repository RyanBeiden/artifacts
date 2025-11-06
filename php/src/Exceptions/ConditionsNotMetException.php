<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ConditionsNotMetException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
