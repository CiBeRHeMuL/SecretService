<?php

namespace App\Domain\Service\Message;

use App\Domain\Dto\Message\CreateMessageDto;
use App\Domain\Entity\Message;
use App\Domain\Exception\ErrorException;
use App\Domain\Helper\HDate;
use App\Domain\Repository\MessageRepositoryInterface;
use App\Domain\Service\Security\EncryptorInterface;
use DateInterval;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class MessageService
{
    private DateInterval $messageLifetime;

    public function __construct(
        private LoggerInterface $logger,
        private EncryptorInterface $messageTextEncryptor,
        private MessageRepositoryInterface $messageRepository,
        string|float|int $messageLifetime,
    ) {
        $this->messageLifetime = HDate::calculateInterval($messageLifetime);
    }

    public function create(CreateMessageDto $dto): Message
    {
        try {
            $message = new Message();
            $now = new DateTimeImmutable()->setMicrosecond(0);

            $message->textHash = $this->messageTextEncryptor->encrypt($dto->text);
            $message->createdAt = $now;
            $message->validUntil = $now->add($this->messageLifetime)->setMicrosecond(0);

            $created = $this->messageRepository->create($message);
            if (!$created) {
                throw ErrorException::new('Не удалось создать сообщение');
            }
            return $message;
        } catch (ErrorException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            throw ErrorException::new('Не удалось создать сообщение');
        }
    }

    public function getById(Uuid $id): Message|null
    {
        return $this->messageRepository->findById($id);
    }

    public function delete(Message $message): void
    {
        try {
            $this->messageRepository->delete($message);
        } catch (ErrorException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            throw ErrorException::new('Не удалось удалить сообщение');
        }
    }
}
