<?php

namespace App\Application\UseCase\Message;

use App\Application\Dto\Message\CreateMessageDto;
use App\Application\Dto\Message\CreateMessageFileDto;
use App\Domain\Dto\File\SavingFile;
use App\Domain\Dto\Message\CreateMessageDto as DomainCreateMessageDtoAlias;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\File\FilesService;
use App\Domain\Service\Message\MessageRemoveSchedulerInterface;
use App\Domain\Service\Message\MessageService;
use App\Domain\Service\Security\EncryptorInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateMessageUseCase
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private MessageService $messageService,
        private EncryptorInterface $messageIdEncryptor,
        private TransactionManagerInterface $transactionManager,
        private LoggerInterface $logger,
        private FilesService $fileService,
        private MessageRemoveSchedulerInterface $messageRemoveScheduler,
    ) {
    }

    /**
     * Создает сообщение и возвращает хеш идентификатора, с помощью которого можно получить доступ к сообщению
     *
     * @param CreateMessageDto $dto
     *
     * @return string
     */
    public function execute(CreateMessageDto $dto): string
    {
        $violations = $this->validator->validate($dto);
        if ($violations->count()) {
            throw new ValidationFailedException($dto, $violations);
        }

        if (trim($dto->text) === '' && $dto->files === []) {
            throw ValidationException::new([
                new ValidationError(
                    'text',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    $this->translator->trans('validation.message.text_and_files_empty'),
                ),
                new ValidationError(
                    'files',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    $this->translator->trans('validation.message.text_and_files_empty'),
                ),
            ]);
        }

        $this->transactionManager->beginTransaction();
        try {
            $message = $this->messageService->create(
                new DomainCreateMessageDtoAlias(
                    trim($dto->text),
                ),
            );

            $hash = $this->messageIdEncryptor->encrypt($message->id->toRfc4122());

            $archiveName = $this->fileService->getArchiveHashByMessage($message);
            $password = $this->fileService->generateArchivePassword($message);

            if ($dto->files !== []) {
                $filesSaved = $this->fileService->saveFilesToArchive(
                    array_map(
                        static fn(CreateMessageFileDto $fileDto) => new SavingFile(
                            $fileDto->filename,
                            $fileDto->tempPath,
                        ),
                        $dto->files,
                    ),
                    $archiveName,
                    $password,
                );
                if (!$filesSaved) {
                    throw ErrorException::new('Не удалось создать сообщение');
                }
            }

            $scheduledForRemove = $this->messageRemoveScheduler->scheduleForRemove($message);
            if (!$scheduledForRemove) {
                throw ErrorException::new('Не удалось создать сообщение');
            }

            $this->transactionManager->commit();
            return $hash;
        } catch (ValidationException|ErrorException $e) {
            $this->transactionManager->rollback();
            throw $e;
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            throw ErrorException::new('Не удалось создать сообщение');
        }
    }
}
