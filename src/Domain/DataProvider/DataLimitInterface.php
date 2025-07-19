<?php

namespace App\Domain\DataProvider;

/**
 * Interface DataLimitInterface предоставляет базовую функциональность для работы с
 * ограничением выборки (limit) и смещением (offset), которые используются
 * для управления объемом данных, возвращаемых запросом.
 */
interface DataLimitInterface
{
    /**
     * Получает значение максимального количества элементов для выборки (limit).
     * Если лимит не установлен - NULL
     *
     * @return int|null Значение лимита выборки.
     */
    public function getLimit(): int|null;

    /**
     * Получает значение смещения выборки (offset).
     *
     * @return int Значение смещения выборки.
     */
    public function getOffset(): int;
}
