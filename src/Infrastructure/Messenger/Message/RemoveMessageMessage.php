<?php

namespace App\Infrastructure\Messenger\Message;

use Symfony\Component\Uid\Uuid;

readonly class RemoveMessageMessage
{
    public function __construct(
        private(set) Uuid $messageId,
        private(set) string|null $archiveHash,
    ) {
    }
}
