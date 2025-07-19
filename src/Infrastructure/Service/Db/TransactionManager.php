<?php

namespace App\Infrastructure\Service\Db;

use App\Domain\Service\Db\TransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

class TransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function beginTransaction(): void
    {
        $this->entityManager
            ->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager
            ->commit();
    }

    public function rollback(): void
    {
        $this->entityManager
            ->rollback();
    }

    /**
     * @template T of mixed
     * @param callable(): T $callback
     *
     * @return T
     */
    public function executeTransaction(callable $callback): mixed
    {
        return $this->entityManager
            ->wrapInTransaction($callback);
    }
}
