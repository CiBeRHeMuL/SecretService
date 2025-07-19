<?php

namespace App\Presentation\Api\Response\Model;

readonly class CreatedMessage
{
    public function __construct(
        private(set) string $url,
        private(set) string $valid_until,
    ) {
    }
}
