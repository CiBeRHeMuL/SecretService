<?php

namespace App\Infrastructure\Service\Message;

use App\Domain\Entity\Message;
use App\Domain\Service\File\FilesService;
use App\Domain\Service\Message\MessageRemoveSchedulerInterface;
use App\Infrastructure\Messenger\Message\RemoveMessageMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;

class MessageRemoveScheduler implements MessageRemoveSchedulerInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private FilesService $filesService,
        private LoggerInterface $logger,
    ) {
    }

    public function scheduleForRemove(Message $message): bool
    {
        try {
            $this->messageBus->dispatch(
                new RemoveMessageMessage(
                    $message->id,
                    $this->filesService->getArchiveHashByMessage($message),
                ),
                [
                    DelayStamp::delayUntil($message->validUntil),
                ],
            );
            return true;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
