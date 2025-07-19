<?php

namespace App\Application\Dto\Message;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Cascade]
readonly class CreateMessageDto
{
    /**
     * @param string $text
     * @param CreateMessageFileDto[] $files
     */
    public function __construct(
        #[SensitiveParameter]
        #[Assert\Type('string')]
        public string $text = '',
        #[Assert\Type('array')]
        #[Assert\Count(max: 10)]
        public array $files = [],
    ) {
    }
}
