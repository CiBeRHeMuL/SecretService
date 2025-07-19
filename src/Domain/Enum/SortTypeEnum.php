<?php

namespace App\Domain\Enum;

use InvalidArgumentException;

enum SortTypeEnum: string
{
    case Asc = 'asc';
    case Desc = 'desc';

    public static function fromPhpSort(int $sort): self
    {
        return match ($sort) {
            SORT_DESC => self::Desc,
            SORT_ASC => self::Asc,
            default => throw new InvalidArgumentException('Invalid sort type!'),
        };
    }

    public function getPhpSort(): int
    {
        return match ($this) {
            self::Asc => SORT_ASC,
            self::Desc => SORT_DESC,
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::Asc => 'По возрастанию',
            self::Desc => 'По убыванию',
        };
    }
}
