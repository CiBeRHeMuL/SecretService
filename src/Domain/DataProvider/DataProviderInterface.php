<?php

namespace App\Domain\DataProvider;

use Iterator;

/**
 * Интерфейс DataProviderInterface определяет методы для работы с
 * провайдером данных, включая получение общего количества элементов,
 * текущего количества элементов в выборке, самих элементов, а также
 * параметров ограничения выборки.
 *
 * @template-covariant T
 */
interface DataProviderInterface
{
    /**
     * Получает общее количество элементов, доступных без limit and offset.
     *
     * @return int Общее количество элементов.
     */
    public function getTotal(): int;

    /**
     * Получает количество элементов, присутствующих в текущей выборке.
     *
     * @return int Количество элементов в текущей выборке.
     */
    public function getItemsCount(): int;

    /**
     * Получает Итератор данных для текущей выборки.
     *
     * @return Iterator<int, T> Итератор данных.
     */
    public function getItems(): Iterator;

    /**
     * Возвращает объект, реализующий интерфейс DataLimitInterface,
     * для работы с параметрами ограничения и смещения выборки данных.
     *
     * @return DataLimitInterface Объект с параметрами ограничения данных.
     */
    public function getDataLimit(): DataLimitInterface;

    /**
     * Возвращает объект, реализующий интерфейс DataSortInterface,
     * для работы с параметрами сортировки данных.
     * Если сортировка не предусмотрена, то NULL.
     *
     * @return DataSortInterface|null
     */
    public function getDataSort(): DataSortInterface|null;
}
