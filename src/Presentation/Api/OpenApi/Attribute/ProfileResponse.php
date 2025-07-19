<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use App\Presentation\Api\Enum\HttpStatusCodeEnum;
use App\Presentation\Api\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ProfileResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: HttpStatusCodeEnum::NoContent->getCode(),
            description: HttpStatusCodeEnum::NoContent->getName(),
            content: new JsonContent(
                ref: new Model(type: RM\Common\ProfileResponse::class),
            ),
        );
    }
}
