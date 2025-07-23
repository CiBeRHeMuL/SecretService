<?php

namespace App\Application\UseCase\Message;

use App\Domain\Service\Message\MessageService;
use App\Domain\Service\Security\EncryptorInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class GetExpirationByHashUseCase
{
    public function __construct(
        private MessageService $messageService,
        private EncryptorInterface $messageIdEncryptor,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(string $hash): DateTimeImmutable|null
    {
        try {
            $id = $this->messageIdEncryptor->decrypt($hash);
            $id = new Uuid($id);
        } catch (Throwable $e) {
            return null;
        }
        try {
            $message = $this->messageService->getById($id);
            return $message !== null && $message->isValid
                ? $message->validUntil
                : null;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }
}
