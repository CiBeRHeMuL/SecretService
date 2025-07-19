<?php

namespace App\Application\UseCase\File;

use App\Application\Dto\File\FilesArchive;
use App\Application\ValueObject\Jwt\DownloadFilesJwtClaims;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\File\FilesService;
use App\Domain\Service\Jwt\JwtServiceInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class DownloadFilesUseCase
{
    public function __construct(
        private JwtServiceInterface $jwtService,
        private FilesService $fileService,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    public function execute(string $hash): FilesArchive|null
    {
        try {
            $claims = $this->jwtService->decode(
                $hash,
                DownloadFilesJwtClaims::class,
                DownloadFilesJwtClaims::SALT,
            );

            if ($claims === null || $claims->validUntil < new DateTimeImmutable()) {
                throw ErrorException::new($this->translator->trans('files.not_found'), 404);
            }

            $exists = $this->fileService->archiveExists($claims->archiveName);
            if (!$exists) {
                return null;
            }

            $decrypted = $this->fileService->decryptArchive($claims->archiveName, $claims->password);
            if (!$decrypted) {
                return null;
            }

            $stream = $this->fileService->readDecryptedArchiveAsRecourse($claims->archiveName);
            if (!$stream) {
                $this->fileService->encryptArchive($claims->archiveName, $claims->password);
                return null;
            }

            $tmpResource = tmpfile();
            stream_copy_to_stream($stream, $tmpResource);
            fclose($stream);

            $deleted = $this->fileService->deleteArchive($claims->archiveName);
            if (!$deleted) {
                $this->fileService->encryptArchive($claims->archiveName, $claims->password);
                return null;
            }

            $originalName = time() . ".zip";

            return new FilesArchive($originalName, $tmpResource);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
