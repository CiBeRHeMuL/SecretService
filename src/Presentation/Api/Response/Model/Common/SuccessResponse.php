<?php

namespace App\Presentation\Api\Response\Model\Common;

use OpenApi\Attributes as OA;

/**
 * Успешный ответ
 */
readonly class SuccessResponse
{
    public function __construct(
        #[OA\Property(
            type: 'object',
            nullable: true,
            anyOf: [
                new OA\Schema(type: 'boolean'),
                new OA\Schema(type: 'integer'),
                new OA\Schema(type: 'number'),
                new OA\Schema(type: 'string'),
                new OA\Schema(type: 'array', items: new OA\Items()),
                new OA\Schema(type: 'object', additionalProperties: true),
            ],
        )]
        public mixed $data = null,
        public Meta $meta = new Meta('dev'),
    ) {
    }
}
