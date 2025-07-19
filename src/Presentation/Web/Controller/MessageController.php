<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Message\CreateMessageDto;
use App\Application\Dto\Message\CreateMessageFileDto;
use App\Application\UseCase\Message\CreateMessageUseCase;
use App\Application\UseCase\Message\GetByHashUseCase;
use App\Domain\Exception\ValidationException;
use App\Presentation\Web\Form\CreateMessageForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageController extends AbstractController
{
    #[Route(['/message', '/'], 'create_message')]
    public function create(
        Request $request,
        CreateMessageUseCase $useCase,
        TranslatorInterface $translator,
    ): Response {
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
                    $hash = $useCase->execute(
                        new CreateMessageDto($text, $preparedFiles),
                    );
                    return $this->redirectToRoute('get_created_message', ['hash' => $hash]);
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
        return $this->render('message/create.html.twig', compact('form'));
    }

    #[Route('/message/created/{hash}', name: 'get_created_message', requirements: ['hash' => '.*'], methods: ['GET'])]
    public function created(
        string $hash,
    ): Response {
        return $this->render('message/created.html.twig', compact('hash'));
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
