<?php

namespace App\Domain\DataProvider;

/**
 * Базовый класс для работы с Limit и Offset
 */
class LimitOffset implements DataLimitInterface
{
    public function __construct(
        protected int|null $limit,
        protected int $offset,
    ) {
    }

    public function getLimit(): int|null
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
