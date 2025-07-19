<?php

namespace App\Application\Dto\Message;

use DateTimeImmutable;

readonly class GetMessageDto
{
    /**
     * @param string $text
     * @param string|null $filesDownloadHash
     * @param DateTimeImmutable $filesValidUntil
     */
    public function __construct(
        private(set) string $text,
        private(set) string|null $filesDownloadHash,
        private(set) DateTimeImmutable $filesValidUntil,
    ) {
    }
}
