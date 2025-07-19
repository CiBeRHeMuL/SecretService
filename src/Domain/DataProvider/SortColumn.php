<?php

namespace App\Domain\DataProvider;

/**
 * Простейшая реализация SortColumnInterface.
 */
class SortColumn implements SortColumnInterface
{
    public function __construct(
        private string $column,
        private string $name,
        private int $sort,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }
}
