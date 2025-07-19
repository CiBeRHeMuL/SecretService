<?php

namespace App\Presentation\Api\Response\Model\Common;

use App\Domain\Helper\HArray;
use App\Domain\Validation\ValidationError;
use App\Presentation\Api\Enum\ErrorSlugEnum;
use InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Ответ с ошибками валидации
 */
readonly class ValidationResponse
{
    /**
     * @param array<string, Error[]> $errors
     */
    public function __construct(
        public array $errors = [],
    ) {
        foreach ($errors as $field => $fieldErrors) {
            if (is_int($field)) {
                throw new InvalidArgumentException('Field name cannot be integer');
            }
        }
    }

    public static function fromViolationList(
        ConstraintViolationListInterface $violations,
    ): self {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = new Error(
                ErrorSlugEnum::WrongField->getSlug(),
                $violation->getParameters()['hint'] ?? $violation->getMessage(),
            );
        }
        return new self($errors);
    }

    /**
     * @param ValidationError[] $errors
     *
     * @return self
     */
    public static function fromValidationErrors(array $errors): self
    {
        return new self(
            HArray::groupExtended(
                $errors,
                fn(ValidationError $e) => $e->getField(),
                projection: fn(ValidationError $e) => new Error($e->getSlug(), $e->getMessage()),
                preserveKeys: false,
            ),
        );
    }
}
