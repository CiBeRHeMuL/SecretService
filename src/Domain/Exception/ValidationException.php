<?php

namespace App\Domain\Exception;

use App\Domain\Validation\ValidationError;
use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException
{
    /** @var ValidationError[] */
    private readonly array $errors;

    /**
     * @param ValidationError[] $errors
     * @param Throwable|null $previous
     *
     * @return self
     */
    public static function new(
        array $errors,
        Throwable|null $previous = null,
    ): self {
        $new = new self(
            'Validation failed',
            422,
            $previous,
        );
        $new->errors = $errors;
        return $new;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
