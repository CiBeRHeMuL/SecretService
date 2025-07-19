<?php

namespace App\Presentation\Api\Controller;

use App\Application\Dto\Message\CreateMessageDto;
use App\Application\Dto\Message\CreateMessageFileDto;
use App\Application\UseCase\Message\CreateMessageUseCase;
use App\Application\UseCase\Message\GetByHashUseCase;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Validation\ValidationError;
use App\Presentation\Api\OpenApi\Attribute as LOA;
use App\Presentation\Api\Response\Model\Common\SuccessResponse;
use App\Presentation\Api\Response\Model\CreatedMessage;
use App\Presentation\Api\Response\Model\ReadMessage;
use App\Presentation\Api\Response\Response;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageController extends AbstractController
{
    /**
     * @param Request $request
     * @param CreateMessageUseCase $useCase
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $urlGenerator
     * @param UploadedFile[] $files
     *
     * @return JsonResponse
     */
    #[Route('/message', name: 'create_message', methods: ['POST'], format: 'form')]
    #[OA\RequestBody(
        content: new OA\MediaType(
            'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        'text',
                        type: 'string',
                        default: '',
                        nullable: true,
                    ),
                    new OA\Property(
                        'files',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string',
                            format: 'binary',
                        ),
                        default: null,
                        nullable: true,
                    ),
                ],
            ),
        ),
    )]
    #[LOA\SuccessResponse(CreatedMessage::class)]
    #[LOA\ValidationResponse]
    public function create(
        Request $request,
        CreateMessageUseCase $useCase,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        #[MapUploadedFile]
        array $files = [],
    ): JsonResponse {
        $preparedFiles = [];
        foreach ($files as $k => $file) {
            if ($file->isValid()) {
                $preparedFiles[] = new CreateMessageFileDto(
                    $file->getPathname(),
                    $file->getClientOriginalPath(),
                );
            } else {
                throw ValidationException::new([
                    new ValidationError(
                        "files[$k]",
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        $translator->trans('validation.message.files.wrong'),
                    ),
                ]);
            }
        }

        $dto = new CreateMessageDto(
            $request->request->get('text', ''),
            $preparedFiles,
        );

        $result = $useCase->execute($dto);

        return Response::success(
            new SuccessResponse(
                new CreatedMessage(
                    $urlGenerator->generate(
                        'api_get_message',
                        ['hash' => $result->hash],
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    ),
                    $result->validUntil->format(DATE_RFC3339),
                ),
            ),
        );
    }

    #[Route('/message/{hash}', name: 'get_message', requirements: ['hash' => '.*'], methods: ['GET'])]
    #[LOA\SuccessResponse(ReadMessage::class)]
    #[LOA\ErrorResponse(404)]
    public function get(
        string $hash,
        GetByHashUseCase $useCase,
    ): JsonResponse {
        $message = $useCase->execute($hash);

        if ($message === null) {
            return Response::notFound();
        }

        return Response::success(
            new SuccessResponse(
                new ReadMessage(
                    $message->text,
                    $message->filesDownloadHash
                        ? $this->generateUrl(
                            'api_download_files',
                            ['hash' => $message->filesDownloadHash],
                            UrlGeneratorInterface::ABSOLUTE_URL,
                        )
                        : null,
                    $message->filesValidUntil?->format(DATE_RFC3339),
                ),
            ),
        );
    }
}
