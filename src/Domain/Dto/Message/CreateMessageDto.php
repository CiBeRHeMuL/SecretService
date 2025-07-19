<?php

namespace App\Domain\Dto\Message;

use SensitiveParameter;

readonly class CreateMessageDto
{
    /**
     * @param string $text
     */
    public function __construct(
        #[SensitiveParameter]
        private(set) string $text = '',
    ) {
    }
}
