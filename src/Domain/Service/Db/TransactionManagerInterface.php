<?php

namespace App\Domain\Service\Db;

interface TransactionManagerInterface
{
    /**
     * Начать транзакцию.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Применить изменения.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Откатить изменения.
     *
     * @return void
     */
    public function rollback(): void;

    /**
     * Обернуть функцию в транзакцию.
     *
     * @template T of mixed
     * @param callable(): T $callback
     *
     * @return T
     */
    public function executeTransaction(callable $callback): mixed;
}
