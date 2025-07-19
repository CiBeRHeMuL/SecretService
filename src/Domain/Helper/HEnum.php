<?php

namespace App\Domain\Helper;

use BackedEnum;
use UnitEnum;

class HEnum
{
    /**
     * @template T of UnitEnum
     * @param class-string<T> $enum
     *
     * @return (T is BackedEnum ? array<value-of<T>> : array<key-of<T>>)
     */
    public static function choices(string $enum): array
    {
        return is_subclass_of($enum, BackedEnum::class, true)
            ? array_map(fn(BackedEnum $e) => $e->value, $enum::cases())
            : array_map(fn(UnitEnum $e) => $e->name, $enum::cases());
    }
}
