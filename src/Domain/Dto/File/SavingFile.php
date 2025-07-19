<?php

namespace App\Domain\Dto\File;

use SensitiveParameter;

readonly class SavingFile
{
    public function __construct(
        #[SensitiveParameter]
        private(set) string $originalName,
        #[SensitiveParameter]
        private(set) string $tempPathname,
    ) {
    }
}
