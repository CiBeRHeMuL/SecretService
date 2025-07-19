<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use App\Presentation\Api\Enum\HttpStatusCodeEnum;
use App\Presentation\Api\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ErrorResponse extends Response
{
    public function __construct(int $statusCode = 400)
    {
        parent::__construct(
            response: $statusCode,
            description: HttpStatusCodeEnum::tryFrom($statusCode)?->getName() ?: 'Internal server error',
            content: new JsonContent(
                ref: new Model(type: RM\Common\ErrorResponse::class),
            ),
        );
    }
}
