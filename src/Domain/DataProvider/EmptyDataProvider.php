<?php

namespace App\Domain\DataProvider;

use Iterator;

class EmptyDataProvider implements DataProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getItems(): Iterator
    {
        yield from [];
    }

    /**
     * @inheritDoc
     */
    public function getDataLimit(): DataLimitInterface
    {
        return new LimitOffset(null, 0);
    }

    /**
     * @inheritDoc
     */
    public function getDataSort(): DataSortInterface|null
    {
        return null;
    }
}
