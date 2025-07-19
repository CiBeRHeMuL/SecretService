<?php

namespace App\Domain\Dto\File;

readonly class ReadFile
{
    public function __construct(
        private(set) string $originalName,
        private(set) string $tempFilename,
    ) {
    }
}
