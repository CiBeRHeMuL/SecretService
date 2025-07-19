<?php

namespace App\Presentation\Api\Response\Model\Common;

readonly class Meta
{
    public function __construct(
        public string $mode,
    ) {
    }
}
