<?php

namespace App\Domain\Validation;

readonly class ValidationError
{
    public function __construct(
        private string $field,
        private string $slug,
        private string $message,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
