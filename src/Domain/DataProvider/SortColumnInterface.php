<?php

namespace App\Domain\DataProvider;

/**
 * Interface SortColumnInterface предоставляет базовую функциональность для работы с
 * колонкой для сортировки.
 */
interface SortColumnInterface
{
    /**
     * Колонка или выражение по которому происходит сортировка.
     *
     * @return string
     */
    public function getColumn(): string;

    /**
     * Название сортировки для отображения
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Тип сортировки.
     *
     * Возвращает одну из констант SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE
     * или их комбинацию
     * @return int
     */
    public function getSort(): int;
}
