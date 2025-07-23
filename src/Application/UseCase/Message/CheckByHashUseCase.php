<?php

namespace App\Application\UseCase\Message;

use App\Domain\Service\Message\MessageService;
use App\Domain\Service\Security\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class CheckByHashUseCase
{
    public function __construct(
        private MessageService $messageService,
        private EncryptorInterface $messageIdEncryptor,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(string $hash): bool
    {
        try {
            $id = $this->messageIdEncryptor->decrypt($hash);
            $id = new Uuid($id);
        } catch (Throwable $e) {
            return false;
        }
        try {
            $message = $this->messageService->getById($id);
            return $message !== null && $message->isValid;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
