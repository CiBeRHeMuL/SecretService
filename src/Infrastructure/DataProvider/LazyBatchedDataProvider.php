<?php

namespace App\Infrastructure\DataProvider;

use App\Domain\DataProvider\DataLimitInterface;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\DataProvider\LimitOffset;
use Closure;
use Iterator;

/**
 * @template T
 * @implements DataProviderInterface<T>
 */
class LazyBatchedDataProvider implements DataProviderInterface
{
    /**
     * @param Closure(DataLimitInterface): Iterator<T> $itemsFetcher функция для ленивой загрузки данных,
     * получает на вход объект DataLimitInterface, возвращает итератор
     * @param int $batchSize размер батча загружаемых данных
     * @param int $count
     * @param int $total
     * @param DataLimitInterface $limit
     * @param DataSortInterface|null $sort
     */
    public function __construct(
        private Closure $itemsFetcher,
        private int $batchSize = 500,
        private int $count = 0,
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
        return $this->count;
    }

    public function getItems(): Iterator
    {
        $i = 0;
        while ($i < $this->count) {
            $limit = new LimitOffset(
                min($this->batchSize, $this->count - $i),
                $i + $this->limit->getOffset(),
            );
            $items = ($this->itemsFetcher)($limit);
            foreach ($items as $item) {
                $i++;
                yield $item;
            }
        }
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
