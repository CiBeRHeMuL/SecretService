<?php

namespace App\Domain\Exception;

use RuntimeException;
use Throwable;

class ErrorException extends RuntimeException
{
    public static function new(
        string $message,
        int $code = 500,
        Throwable|null $previous = null,
    ): self {
        return new self($message, $code, $previous);
    }
}
