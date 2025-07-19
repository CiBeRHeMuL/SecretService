<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use App\Presentation\Api\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SuccessPaginationResponse extends Response
{
    public function __construct(
        string|null $dataModel,
    ) {
        parent::__construct(
            response: 200,
            description: 'OK',
            content: new JsonContent(
                properties: [
                    new Property(
                        'data',
                        properties: [
                            new Property(
                                'items',
                                type: 'array',
                                items: $dataModel === null || $dataModel === 'null'
                                    ? new Items(
                                        type: 'null',
                                    )
                                    : new Items(
                                        ref: new Model(type: $dataModel),
                                    ),
                            ),
                        ],
                        type: 'object',
                    ),
                    new Property(
                        'meta',
                        ref: new Model(type: RM\Common\Meta::class),
                    ),
                ],
                type: 'object',
            ),
        );
    }
}
