<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Message;
use Symfony\Component\Uid\Uuid;

interface MessageRepositoryInterface extends Common\RepositoryInterface
{
    public function findById(Uuid $id): Message|null;
}
