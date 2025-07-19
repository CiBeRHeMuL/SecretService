<?php

namespace App\Domain\DataProvider;

/**
 * Класс для работы с Limit и Offset на основе пагинации Page/PerPage
 */
class LimitPage implements DataLimitInterface
{
    public function __construct(
        protected int $page,
        protected int $perPage,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): ?int
    {
        return $this->perPage;
    }

    /**
     * @inheritDoc
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
