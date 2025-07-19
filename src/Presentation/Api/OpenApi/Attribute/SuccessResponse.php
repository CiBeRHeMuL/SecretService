<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use App\Presentation\Api\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SuccessResponse extends Response
{
    public function __construct(
        string|null $dataModel = null,
    ) {
        $isTypedData = !in_array(
            $dataModel,
            [
                'boolean',
                'integer',
                'number',
                'string',
                'array',
                'object',
            ],
        );
        parent::__construct(
            response: 200,
            description: 'OK',
            content: new JsonContent(
                properties: [
                    $dataModel === null || $dataModel === 'null'
                        ? new Property(
                        'data',
                        type: 'null',
                    )
                        : new Property(
                        'data',
                        ref: $isTypedData ? new Model(type: $dataModel) : null,
                        type: $isTypedData ? null : $dataModel,
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
