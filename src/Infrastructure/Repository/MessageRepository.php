<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Message;
use App\Domain\Repository\MessageRepositoryInterface;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class MessageRepository extends Common\AbstractRepository implements MessageRepositoryInterface
{
    public function findById(Uuid $id): Message|null
    {
        $q = Query::select()
            ->from($this->getClassTable(Message::class))
            ->where(['id' => $id]);
        return $this->findOneByQuery($q, Message::class);
    }
}
