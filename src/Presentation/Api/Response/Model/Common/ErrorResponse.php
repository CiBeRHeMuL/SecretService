<?php

namespace App\Presentation\Api\Response\Model\Common;

/**
 * Ответ с ошибкой
 */
readonly class ErrorResponse
{
    public function __construct(
        public Error $error,
    ) {
    }
}
