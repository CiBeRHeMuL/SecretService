<?php

namespace App\Presentation\Api\Response\Model\Common;

readonly class Error
{
    public function __construct(
        public string $slug,
        public string $message,
    ) {
    }
}
