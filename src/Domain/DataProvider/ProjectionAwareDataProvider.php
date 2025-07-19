<?php

namespace App\Domain\DataProvider;

use App\Domain\Iterator\ProjectionIterator;
use Closure;
use Iterator;

/**
 * @template-covariant TItem
 * @template-covariant TProj
 * @template-implements DataProviderInterface<TProj>
 */
class ProjectionAwareDataProvider implements DataProviderInterface
{
    /**
     * @param DataProviderInterface $dataProvider
     * @param Closure(TItem): TProj $projection
     */
    public function __construct(
        private DataProviderInterface $dataProvider,
        private Closure $projection,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->dataProvider->getTotal();
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return $this->dataProvider->getItemsCount();
    }

    /**
     * @return Iterator<int, TProj>
     */
    public function getItems(): Iterator
    {
        yield from new ProjectionIterator(
            $this->dataProvider->getItems(),
            $this->projection,
        );
    }

    /**
     * @inheritDoc
     */
    public function getDataLimit(): DataLimitInterface
    {
        return $this->dataProvider->getDataLimit();
    }

    /**
     * @inheritDoc
     */
    public function getDataSort(): DataSortInterface|null
    {
        return $this->dataProvider->getDataSort();
    }
}
