<?php

namespace App\Domain\Service\Message;

use App\Domain\Entity\Message;

interface MessageRemoveSchedulerInterface
{
    public function scheduleForRemove(Message $message): bool;
}
