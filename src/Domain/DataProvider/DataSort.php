<?php

namespace App\Domain\DataProvider;

/**
 * Простейшая реализация DataSortInterface.
 */
class DataSort implements DataSortInterface
{
    /**
     * @param SortColumnInterface[] $columns
     */
    public function __construct(
        private array $columns,
    ) {
    }

    public function getSortColumns(): array
    {
        return $this->columns;
    }
}
