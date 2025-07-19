<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use Attribute;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\Mime\MimeTypes;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FileResponse extends Response
{
    public function __construct(array $contentTypes)
    {
        $content = [];
        foreach ($contentTypes as $contentType) {
            $contentType = (new MimeTypes())->getMimeTypes($contentType)[0] ?? $contentType;
            $content[] = new MediaType(
                $contentType,
                schema: new Schema(
                    type: 'string',
                    format: 'binary',
                ),
            );
        }
        parent::__construct(
            response: 200,
            description: 'Ok',
            content: $content,
        );
    }
}
