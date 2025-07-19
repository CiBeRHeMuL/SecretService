<?php

namespace App\Infrastructure\Db\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Throwable;

class TextArrayType extends Type
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string|null
    {
        if ($value === null) {
            return null;
        }
        try {
            if ($platform instanceof PostgreSQLPlatform) {
                $value = array_map(
                    fn($v) => $v === null ? 'null' : '"' . addcslashes($v, '"') . '"',
                    $value,
                );
                return '{' . implode(',', $value) . '}';
            } else {
                return implode(',', $value);
            }
        } catch (Throwable $e) {
            throw SerializationFailed::new(
                $value,
                'text[]',
                'Value must be an array of strings',
                $e,
            );
        }
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return string[]|null
     * @throws ValueNotConvertible
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array|null
    {
        if ($value === null) {
            return null;
        }
        try {
            if ($platform instanceof PostgreSQLPlatform) {
                if ($value === '{}') {
                    return [];
                }
                $value = substr($value, 1, -1);
                preg_match_all('/"(?:[^"\\\\]|\\\\.)*"|[^,]+/', $value, $matches);
                return array_map(
                    static function ($val) {
                        if ($val === 'NULL' || $val === 'null') {
                            return null;
                        }
                        return str_replace('\\"', '"', trim($val, '"'));
                    },
                    $matches[0],
                );
            }
            return explode(',', $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                'string[]',
                $e,
            );
        }
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return ['text[]', '_text'];
        } else {
            return [Types::TEXT];
        }
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return 'text[]';
        } else {
            return $platform->getStringTypeDeclarationSQL($column);
        }
    }
}
