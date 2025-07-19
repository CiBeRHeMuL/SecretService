<?php

namespace App\Presentation\Api\OpenApi\Attribute;

use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\Property;
use UnitEnum;

#[Attribute(
    Attribute::TARGET_METHOD
    | Attribute::TARGET_PROPERTY
    | Attribute::TARGET_PARAMETER
    | Attribute::TARGET_CLASS_CONSTANT
    | Attribute::IS_REPEATABLE
)]
class Enum extends Property
{
    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public function __construct(
        string $enumClass,
    ) {
        parent::__construct(
            ref: new Model(type: $enumClass),
        );
    }
}
