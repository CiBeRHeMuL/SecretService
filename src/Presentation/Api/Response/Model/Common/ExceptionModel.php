<?php

namespace App\Presentation\Api\Response\Model\Common;

use OpenApi\Attributes as OA;
use Throwable;

readonly class ExceptionModel
{
    public function __construct(
        public string $exception_class,
        public int $code,
        public string $file,
        public int $line,
        public string $message,
        #[OA\Property(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property('function', type: 'string'),
                    new OA\Property('line', type: 'integer'),
                    new OA\Property('file', type: 'string'),
                    new OA\Property('class', type: 'string', nullable: true),
                    new OA\Property('object', type: 'object', nullable: true, additionalProperties: true),
                    new OA\Property('type', type: 'string', nullable: true),
                    new OA\Property('args', type: 'array', items: new OA\Items(type: 'string')),
                ],
                type: 'object',
            )
        )]
        public array $trace,
    ) {
    }

    public static function fromThrowable(Throwable $e): self
    {
        return new self(
            $e::class,
            $e->getCode(),
            $e->getFile(),
            $e->getLine(),
            $e->getMessage(),
            $e->getTrace(),
        );
    }
}
