<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class ImportRequestBody extends OA\RequestBody
{
    /**
     * @param class-string<object> $dtoClass
     */
    public function __construct(
        string $dtoClass,
    ) {
        parent::__construct(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    allOf: [
                        new OA\Schema(
                            ref: new Model(type: $dtoClass),
                            type: 'object',
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'file',
                                    type: 'string',
                                    format: 'binary',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        );
    }
}
