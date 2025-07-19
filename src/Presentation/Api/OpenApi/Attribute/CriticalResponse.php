<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use App\Presentation\Api\Enum\HttpStatusCodeEnum;
use App\Presentation\Api\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class CriticalResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 500,
            description: HttpStatusCodeEnum::InternalServerError->getName(),
            content: new JsonContent(
                ref: new Model(type: RM\Common\CriticalResponse::class),
            ),
        );
    }
}
