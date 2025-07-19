<?php

namespace App\Domain\DataProvider;

/**
 * Interface DataSortInterface предоставляет базовую функциональность для работы с
 * сортировкой выборки, возвращаемой запросом.
 */
interface DataSortInterface
{
    /**
     * Возвращает колонки для сортировки.
     *
     * @return SortColumnInterface[]
     */
    public function getSortColumns(): array;
}
