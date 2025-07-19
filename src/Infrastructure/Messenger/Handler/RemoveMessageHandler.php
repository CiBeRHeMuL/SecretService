<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Domain\Service\File\FilesService;
use App\Domain\Service\Message\MessageService;
use App\Infrastructure\Messenger\Message\RemoveMessageMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveMessageHandler
{
    public function __construct(
        private MessageService $messageService,
        private FilesService $filesService,
    ) {
    }

    public function __invoke(RemoveMessageMessage $message): void
    {
        $msg = $this->messageService->getById($message->messageId);
        if ($msg !== null) {
            $this->messageService->delete($msg);
        }

        if ($this->filesService->archiveExists($message->archiveHash)) {
            $this->filesService->deleteArchive($message->archiveHash);
        }
    }
}
