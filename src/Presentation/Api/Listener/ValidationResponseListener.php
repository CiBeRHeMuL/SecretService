<?php

namespace App\Presentation\Api\Listener;

use App\Domain\Exception\ValidationException;
use App\Presentation\Api\Enum\ErrorSlugEnum;
use App\Presentation\Api\Response\Model\Common\Error;
use App\Presentation\Api\Response\Model\Common\ErrorResponse;
use App\Presentation\Api\Response\Model\Common\ValidationResponse;
use App\Presentation\Api\Response\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationResponseListener
{
    public function __construct(
        private string $pathPattern = '^.*$',
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if (!preg_match("~$this->pathPattern~", $event->getRequest()->getPathInfo())) {
            return;
        }
        $e = $event->getThrowable();
        if ($e instanceof UnprocessableEntityHttpException || $e instanceof HttpException && $e->getStatusCode() === 422) {
            $ve = $e->getPrevious();
            if ($ve instanceof ValidationFailedException) {
                $violations = $ve->getViolations();
                $event->setResponse(
                    Response::validation(
                        ValidationResponse::fromViolationList($violations),
                    ),
                );
                $event->stopPropagation();
            } elseif ($ve === null) {
                $event->setResponse(
                    Response::error(
                        new ErrorResponse(
                            new Error(
                                ErrorSlugEnum::BadRequest->getSlug(),
                                'Пропущены обязательный поля',
                            ),
                        ),
                    ),
                );
                $event->stopPropagation();
            }
        } elseif ($e instanceof ValidationFailedException) {
            $violations = $e->getViolations();
            $event->setResponse(
                Response::validation(
                    ValidationResponse::fromViolationList($violations),
                ),
            );
            $event->stopPropagation();
        } elseif ($e instanceof ValidationException) {
            $errors = $e->getErrors();
            $event->setResponse(
                Response::validation(
                    ValidationResponse::fromValidationErrors($errors),
                ),
            );
            $event->stopPropagation();
        }
    }
}
