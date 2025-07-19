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
    ) {
    }

    public function execute(string $hash): GetMessageDto|null
    {
        try {
            $id = $this->messageIdEncryptor->decrypt($hash);

            $message = $this->messageService->getById(new Uuid($id));
            if (!$message || $message->validUntil < new DateTimeImmutable()) {
                return null;
            }

            $result = new GetMessageDto(
                $this->messageTextEncryptor->decrypt($message->textHash),
                $this->generateDownloadFilesHash($message),
            );

            $this->messageService->delete($message);

            return $result;
        } catch (Throwable $e) {
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
