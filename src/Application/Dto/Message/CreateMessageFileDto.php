<?php

namespace App\Application\Dto\Message;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateMessageFileDto
{
    public function __construct(
        #[Assert\Type('string')]
        public string $tempPath,
        #[SensitiveParameter]
        #[Assert\Type('string')]
        public string $filename,
    ) {
    }
}
