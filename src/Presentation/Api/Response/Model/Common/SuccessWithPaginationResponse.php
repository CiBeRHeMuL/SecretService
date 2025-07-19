<?php

namespace App\Presentation\Api\Response\Model\Common;

/**
 * Успешный ответ с пагинацией
 */
readonly class SuccessWithPaginationResponse
{
    public function __construct(
        public PaginatedData $data = new PaginatedData(),
        public Meta $meta = new Meta('dev'),
    ) {
    }
}
