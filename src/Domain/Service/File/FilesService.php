<?php

namespace App\Domain\Service\File;

use App\Domain\Dto\File\SavingFile;
use App\Domain\Entity\Message;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Throwable;
use ZipArchive;

class FilesService
{
    public function __construct(
        private LoggerInterface $logger,
        private string $filesDir,
    ) {
        $this->filesDir = rtrim($this->filesDir, '/') . '/';
    }

    public function getArchiveHashByMessage(Message $message): string
    {
        return $message->id->toBase58();
    }

    /**
     * @param SavingFile[] $files
     * @param string $archiveName
     * @param string $password
     *
     * @return bool
     */
    public function saveFilesToArchive(
        #[SensitiveParameter]
        array $files,
        #[SensitiveParameter]
        string $archiveName,
        #[SensitiveParameter]
        string $password,
    ): bool {
        $archivePathname = $this->getArchivePathname($archiveName);
        $archive = new ZipArchive();
        try {
            $archive->open($archivePathname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($files as $file) {
                $archive->addFile($file->tempPathname, $file->originalName);
                $archive->setEncryptionName(
                    $file->originalName,
                    ZipArchive::EM_AES_256,
                    $password,
                );
            }
            return $archive->close();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $archive->close();
            return false;
        }
    }

    public function generateArchivePassword(Message $message): string
    {
        return $message->id->toBase58() . $message->validUntil->getTimestamp();
    }

    /**
     * Дешифрует архив, то есть убирает пароль
     *
     * @param string $archiveName
     * @param string $password
     *
     * @return bool
     */
    public function decryptArchive(
        string $archiveName,
        #[SensitiveParameter]
        string $password,
    ): bool {
        $archivePathname = $this->getArchivePathname($archiveName);
        $archive = new ZipArchive();
        try {
            $archive->open($archivePathname);
            $archive->setPassword($password);
            foreach (range(0, $archive->count() - 1) as $i) {
                $archive->setEncryptionIndex($i, ZipArchive::EM_NONE);
            }
            return $archive->close();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $archive->close();
            return false;
        }
    }

    /**
     * Дешифрует архив, то есть убирает пароль
     *
     * @param string $archiveName
     * @param string $password
     *
     * @return bool
     */
    public function encryptArchive(
        string $archiveName,
        #[SensitiveParameter]
        string $password,
    ): bool {
        $archivePathname = $this->getArchivePathname($archiveName);
        $archive = new ZipArchive();
        try {
            $archive->open($archivePathname);
            foreach (range(0, $archive->count() - 1) as $i) {
                $archive->setEncryptionIndex(
                    $i,
                    ZipArchive::EM_AES_256,
                    $password,
                );
            }
            return $archive->close();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $archive->close();
            return false;
        }
    }

    /**
     * Читает архив в поток
     *
     * @param string $archiveName
     *
     * @return resource|null
     */
    public function readDecryptedArchiveAsRecourse(
        string $archiveName,
    ) {
        $archivePathname = $this->getArchivePathname($archiveName);
        $file = fopen($archivePathname, 'rb');
        return $file ?: null;
    }

    public function deleteArchive(string $archiveName): bool
    {
        $archivePathname = $this->getArchivePathname($archiveName);
        return unlink($archivePathname);
    }

    public function archiveExists(string $archiveName): bool
    {
        return file_exists($this->getArchivePathname($archiveName));
    }

    private function getArchivePathname(string $archiveName): string
    {
        return "$this->filesDir$archiveName.zip";
    }
}
