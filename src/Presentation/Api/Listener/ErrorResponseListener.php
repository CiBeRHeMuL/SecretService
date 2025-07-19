<?php

namespace App\Presentation\Api\Listener;

use App\Domain\Exception\ErrorException;
use App\Presentation\Api\Enum\ErrorSlugEnum;
use App\Presentation\Api\Enum\HttpStatusCodeEnum;
use App\Presentation\Api\Response\Model\Common\CriticalResponse;
use App\Presentation\Api\Response\Model\Common\Error;
use App\Presentation\Api\Response\Model\Common\ErrorResponse;
use App\Presentation\Api\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ErrorResponseListener
{
    public function __construct(
        private bool $debug,
        private LoggerInterface $logger,
        private string $pathPattern = '^.*$',
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if (!preg_match("~$this->pathPattern~", $event->getRequest()->getPathInfo())) {
            return;
        }
        $e = $event->getThrowable();
        if ($e instanceof HttpException && $e->getStatusCode() !== 500) {
            $code = HttpStatusCodeEnum::tryFrom($e->getStatusCode()) ?? HttpStatusCodeEnum::InternalServerError;
            $message = $e->getMessage();
            if ($e->getCode() !== 0) {
                $messageCode = HttpStatusCodeEnum::tryFrom($e->getCode());
                if ($messageCode !== null) {
                    $message = ErrorSlugEnum::fromCode($code->getCode())->getSlug();
                }
            }
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(ErrorSlugEnum::fromCode($code->getCode())->getSlug(), $message),
                    ),
                    $code,
                ),
            );
        } elseif ($e instanceof ErrorException) {
            $code = HttpStatusCodeEnum::tryFrom($e->getCode()) ?? HttpStatusCodeEnum::InternalServerError;
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(ErrorSlugEnum::fromCode($code->getCode())->getSlug(), $e->getMessage()),
                    ),
                    $code,
                ),
            );
        } elseif ($e instanceof AccessDeniedException) {
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(ErrorSlugEnum::Unauthorized->getSlug(), HttpStatusCodeEnum::Unauthorized->getName()),
                    ),
                    HttpStatusCodeEnum::Unauthorized,
                ),
            );
        } else {
            $this->logger->error($e);
            if ($this->debug) {
                $event->setResponse(Response::critical(new CriticalResponse($e)));
            } else {
                $event->setResponse(
                    Response::error(
                        new ErrorResponse(
                            new Error(
                                ErrorSlugEnum::InternalServerError->getSlug(),
                                'Internal service error! Please, contact with IT!',
                            ),
                        ),
                        HttpStatusCodeEnum::InternalServerError,
                    ),
                );
            }
        }
        $event->stopPropagation();
    }
}
