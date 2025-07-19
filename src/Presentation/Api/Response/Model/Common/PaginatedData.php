<?php

namespace App\Presentation\Api\Response\Model\Common;

/**
 * Поле data для успешного ответа с пагинацией
 */
readonly class PaginatedData
{
    /**
     * @param array $items
     * @param int|null $offset
     * @param int|null $limit
     * @param int|null $count
     * @param string|null $sort_by
     * @param string|null $sort_type
     */
    public function __construct(
        public array $items = [],
        public int|null $offset = null,
        public int|null $limit = null,
        public int|null $count = null,
        public string|null $sort_by = null,
        public string|null $sort_type = null,
    ) {
    }
}
