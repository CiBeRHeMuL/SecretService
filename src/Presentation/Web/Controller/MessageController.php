<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Message\CreateMessageDto;
use App\Application\Dto\Message\CreateMessageFileDto;
use App\Application\UseCase\Message\CreateMessageUseCase;
use App\Application\UseCase\Message\GetByHashUseCase;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HDate;
use App\Presentation\Web\Form\CreateMessageForm;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageController extends AbstractController
{
    #[Route('/', 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('create_message');
    }

    #[Route('/message', 'create_message')]
    public function create(
        Request $request,
        CreateMessageUseCase $useCase,
        TranslatorInterface $translator,
        #[Autowire('%env(MESSAGE_LIFETIME)%')]
        string|int|float $messageLifetime,
        LoggerInterface $logger,
    ): Response {
        $logger->error($request->attributes->get('_locale'));
        $messageLifetime = HDate::formatInterval(HDate::calculateInterval($messageLifetime), true, true);
        $form = $this->createForm(CreateMessageForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $files = $data['files'];
            $text = $data['text'];

            $preparedFiles = [];
            foreach ($files as $k => $file) {
                if ($file->isValid()) {
                    $preparedFiles[] = new CreateMessageFileDto(
                        $file->getPathname(),
                        $file->getClientOriginalPath(),
                    );
                } else {
                    $form['files']->addError(
                        new FormError($translator->trans('validation.message.files.wrong')),
                    );
                }
            }

            if ($form->getErrors(true)->count() === 0) {
                try {
                    $createdMessage = $useCase->execute(
                        new CreateMessageDto($text, $preparedFiles),
                    );
                    return $this->redirectToRoute(
                        'get_created_message',
                        [
                            'hash' => $createdMessage->hash,
                            'vu' => $createdMessage->validUntil->format(DATE_RFC3339),
                        ],
                    );
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $error) {
                        $field = match ($error->getField()) {
                            'text' => 'text',
                            default => 'files',
                        };
                        $form[$field]->addError(new FormError($error->getMessage()));
                    }
                } catch (ValidationFailedException $e) {
                    foreach ($e->getViolations() as $error) {
                        $field = match ($error->getPropertyPath()) {
                            'text' => 'text',
                            default => 'files',
                        };
                        $form[$field]->addError(new FormError($error->getMessage()));
                    }
                }
            }
        }
        return $this->render('message/create.html.twig', compact('form', 'messageLifetime'));
    }

    #[Route('/message/created/{hash}', name: 'get_created_message', requirements: ['hash' => '.*'], methods: ['GET'])]
    public function created(
        string $hash,
        #[MapQueryParameter('vu')]
        string $validUntil,
    ): Response {
        $validUntil = DateTimeImmutable::createFromFormat(DATE_RFC3339, $validUntil);
        $validUntil ??= new DateTimeImmutable();
        if ($validUntil <= new DateTimeImmutable()) {
            return $this->render('message/error/not_found.html.twig');
        }
        $messageLifetime = new DateTimeImmutable()->diff($validUntil);
        return $this->render('message/created.html.twig', compact('hash', 'messageLifetime'));
    }

    #[Route('/message/{hash}', name: 'get_message', requirements: ['hash' => '.*'], methods: ['GET'])]
    public function read(
        string $hash,
        GetByHashUseCase $useCase,
    ): Response {
        $message = $useCase->execute($hash);
        if ($message === null) {
            return $this->render('message/error/not_found.html.twig');
        }
        return $this->render('message/read.html.twig', compact('message'));
    }
}
