<?php

namespace App\Presentation\Api\Response\Model;

readonly class ReadMessage
{
    public function __construct(
        private(set) string $text,
        private(set) string|null $files_download_url,
        private(set) string|null $files_valid_until,
    ) {
    }
}
