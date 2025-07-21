<?php

namespace App\Application\UseCase\Message;

use App\Application\Dto\Message\GetMessageDto;
use App\Application\ValueObject\Jwt\DownloadFilesJwtClaims;
use App\Domain\Entity\Message;
use App\Domain\Service\File\FilesService;
use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\Service\Message\MessageService;
use App\Domain\Service\Security\EncryptorInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class GetByHashUseCase
{
    public function __construct(
        private MessageService $messageService,
        private EncryptorInterface $messageIdEncryptor,
        private EncryptorInterface $messageTextEncryptor,
        private JwtServiceInterface $jwtService,
        private FilesService $fileService,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(string $hash): GetMessageDto|null
    {
        try {
            $id = $this->messageIdEncryptor->decrypt($hash);
        } catch (Throwable $e) {
            return null;
        }
        try {
            $message = $this->messageService->getById(new Uuid($id));
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
        try {
            if (!$message || $message->validUntil < new DateTimeImmutable()) {
                return null;
            }

            $filesDownloadHash = $this->generateDownloadFilesHash($message);

            $result = new GetMessageDto(
                $this->messageTextEncryptor->decrypt($message->textHash),
                $filesDownloadHash,
                $filesDownloadHash ? $message->validUntil : null,
            );

            $this->messageService->delete($message);

            return $result;
        } catch (Throwable $e) {
            $this->logger->error($e);
            return null;
        }
    }

    private function generateDownloadFilesHash(Message $message): string|null
    {
        if (!$this->fileService->archiveExists($this->fileService->getArchiveHashByMessage($message))) {
            return null;
        }
        $claims = new DownloadFilesJwtClaims(
            $this->fileService->getArchiveHashByMessage($message),
            $this->fileService->generateArchivePassword($message),
            $message->validUntil,
        );
        return $this->jwtService->encode($claims);
    }
}
