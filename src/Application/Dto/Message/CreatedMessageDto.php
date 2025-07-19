<?php

namespace App\Application\Dto\Message;

use DateTimeImmutable;

readonly class CreatedMessageDto
{
    public function __construct(
        private(set) string $hash,
        private(set) DateTimeImmutable $validUntil,
    ) {
    }
}
