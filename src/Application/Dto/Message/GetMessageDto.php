<?php

namespace App\Application\Dto\Message;

readonly class GetMessageDto
{
    /**
     * @param string $text
     * @param string|null $filesDownloadHash
     */
    public function __construct(
        private(set) string $text,
        private(set) string|null $filesDownloadHash,
    ) {
    }
}
