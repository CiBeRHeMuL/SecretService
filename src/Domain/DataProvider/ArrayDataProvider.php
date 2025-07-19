<?php

namespace App\Domain\DataProvider;

use ArrayIterator;
use Iterator;

/**
 * Простейшая реализация DataProviderInterface
 */
class ArrayDataProvider implements DataProviderInterface
{
    public function __construct(
        private array $items = [],
        private int $total = 0,
        private DataLimitInterface $limit = new LimitOffset(null, 0),
        private DataSortInterface|null $sort = null,
    ) {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getItemsCount(): int
    {
        return count($this->items);
    }

    public function getItems(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    public function getDataLimit(): DataLimitInterface
    {
        return $this->limit;
    }

    public function getDataSort(): DataSortInterface|null
    {
        return $this->sort;
    }
}
