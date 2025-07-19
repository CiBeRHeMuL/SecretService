<?php

namespace App\Infrastructure\Repository\Common;

use App\Domain\DataProvider\ArrayDataProvider;
use App\Domain\DataProvider\DataLimitInterface;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\DataSortInterface;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumnInterface;
use App\Domain\Helper\HArray;
use App\Domain\Repository\Common\RepositoryInterface;
use App\Infrastructure\DataProvider\LazyBatchedDataProvider;
use ArrayIterator;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query as DQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Psr\Log\LoggerInterface;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Query\QueryInterface;
use Qstart\Db\QueryBuilder\DML\Query\SelectQuery;
use Qstart\Db\QueryBuilder\Helper\DialectSQL;
use Qstart\Db\QueryBuilder\Query;
use Throwable;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
    ) {
    }

    public function getClassTable(string $class): string|null
    {
        return $this->getEntityManager()
            ->getClassMetadata($class)
            ?->getTableName();
    }

    public function create(object $entity): bool
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return true;
        } catch (Exception $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            return false;
        }
    }

    public function update(object $entity): bool
    {
        try {
            $this->entityManager->flush();
            return true;
        } catch (Exception $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            return false;
        }
    }

    public function delete(object $entity): bool
    {
        try {
            $em = $this->getEntityManager();
            $uow = $em->getUnitOfWork();
            $metadata = $em->getClassMetadata($entity::class);
            $conn = $em->getConnection();
            $id = $uow->getEntityIdentifier($entity);

            $where = [];
            foreach ($id as $field => $value) {
                $column = $metadata->getColumnName($field);
                $where[$column] = $conn->convertToDatabaseValue(
                    $value,
                    $metadata->getTypeOfField($field),
                );
            }
            $query = Query::delete()
                ->from($this->getClassTable($entity::class))
                ->where($where);
            $this->executeQuery($query);
            $uow->detach($entity);
            return true;
        } catch (Exception $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            return false;
        }
    }

    /**
     * @param array $entities
     * @param array $replaceTables
     * @param bool $generateId нужно ли генерировать идентификаторы сущностям.
     * Нужно для вставки во временные таблицы, если генерация идентификаторов уже была, и надо просто заинсертить данные
     *
     * @return int
     * @throws Throwable
     * @throws \Doctrine\DBAL\Exception
     * @throws MappingException
     */
    public function createMulti(array $entities, array $replaceTables = [], bool $generateId = true): int
    {
        if (empty($entities)) {
            return 0;
        }

        $em = $this->entityManager;
        $conn = $this->entityManager->getConnection();

        // Строки для вставки
        /** @var array<string, array<int, array>> $inserts */
        $inserts = [];
        // Сущности, для которых идентификаторы проставляются после вставки
        /** @var array<string, array<int, object>> $postInsertIdEntities */
        $postInsertIdEntities = [];
        // Мапа с полями сущности и колонками идентификаторов для таблиц, для которых проставляются они после вставки
        /** @var array<string, string[]> $idColumnsMap */
        $idColumnsMap = [];
        // Количества полей в сущностях (таблица - количество) для наиболее эффективной вставки
        /** @var array<string, int> $entityFieldCounts */
        $entityFieldCounts = [];
        foreach ($entities as $k => &$entity) {
            $metadata = $em->getClassMetadata($entity::class);
            $tableName = $metadata->getTableName();
            // Если есть таблица для замены, то используем ее
            if (isset($replaceTables[$tableName])) {
                $tableName = $replaceTables[$tableName];
            }

            $rowData = [];
            $idGen = $metadata->idGenerator;
            if ($generateId) {
                if ($idGen->isPostInsertGenerator()) {
                    $idColumnsMap[$tableName] = $metadata->getIdentifierColumnNames();
                    $postInsertIdEntities[$tableName][$k] = $entity;
                } else {
                    $idValue = $idGen->generateId($em, $entity);

                    if (!$idGen instanceof AssignedGenerator) {
                        $idValue = [
                            $metadata->getSingleIdentifierFieldName() => $conn->convertToPHPValue(
                                $idValue,
                                $metadata->getTypeOfField($metadata->getSingleIdentifierFieldName()),
                            ),
                        ];
                        $metadata->setIdentifierValues($entity, $idValue);
                    }
                }
            }

            $entityFieldCounts[$tableName] ??= 0;
            $fieldNames = $metadata->getFieldNames();
            $fieldsCount = 0;
            foreach ($fieldNames as $fieldName) {
                // Пропускаем автогенирируемые во время вставки идентификаторы
                if ($generateId && $metadata->isIdentifier($fieldName) && $idGen->isPostInsertGenerator()) {
                    continue;
                }
                $rowData[$metadata->getColumnName($fieldName)] = $conn->convertToDatabaseValue(
                    $metadata->getFieldValue($entity, $fieldName),
                    $metadata->getTypeOfField($fieldName),
                );
                $fieldsCount++;
            }
            $entityFieldCounts[$tableName] = $fieldsCount;
            $inserts[$tableName][$k] = $rowData;
        }

        // Выполнение SQL-запроса
        $conn->beginTransaction();
        $cnt = 0;
        try {
            foreach ($inserts as $tableName => $rows) {
                // Разбиваем строки на чанки, чтобы не улететь за лимит в 65535 параметров
                // Сохраняем ключи чтобы потом можно было связать строку из вставки с сущностью, если надо будет проставлять идентификаторы
                $chunks = array_chunk(
                    $rows,
                    floor(65535 / ($entityFieldCounts[$tableName] ?? 100)),
                    true,
                );
                foreach ($chunks as $chunk) {
                    $query = Query::insert()->into($tableName)->addMultipleValues($chunk);
                    if (!empty($postInsertIdEntities[$tableName])) {
                        $columns = $idColumnsMap[$tableName];
                        $query->setEndOfQuery(' RETURNING "' . implode('", "', $columns) . '"');
                    }
                    $result = $this->executeQuery($query);
                    $cnt += $result->rowCount();

                    // Если есть сущности, для которых идентификаторы проставляются после вставки, то проставляем им идентификаторы
                    if ($generateId && !empty($postInsertIdEntities[$tableName])) {
                        // Колонки ключей
                        $columns = $idColumnsMap[$tableName];
                        // Первичные ключи для связывания
                        $allIds = $result->fetchAllAssociative();

                        $entities = &$postInsertIdEntities[$tableName];
                        $metadata = $em->getClassMetadata($entities[array_key_first($entities)]::class);
                        $idsKey = 0;
                        foreach (array_keys($chunk) as $k) {
                            $idValues = $allIds[$idsKey];
                            // Удаляем ссылку на предыдущую сущность
                            unset($entity);
                            $entity = &$entities[$k];

                            array_walk(
                                $idValues,
                                function (mixed &$id, string $column) use (&$conn, &$metadata) {
                                    $id = $conn->convertToPHPValue(
                                        $id,
                                        $metadata->getTypeOfField($metadata->getFieldName($column)),
                                    );
                                },
                            );

                            $metadata->setIdentifierValues(
                                $entity,
                                array_combine(
                                    array_map(
                                        $metadata->getFieldName(...),
                                        $columns,
                                    ),
                                    $idValues,
                                ),
                            );
                            $idsKey++;
                        }
                    }
                }
            }

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }

        return $cnt;
    }

    public function createMultiReturningIds(array $entities, array $replaceTables = [], bool $generateId = true): array
    {
        if (empty($entities)) {
            return [];
        }

        $em = $this->entityManager;
        $conn = $this->entityManager->getConnection();

        // Строки для вставки
        /** @var array<string, array<int, array>> $inserts */
        $inserts = [];
        // Сущности, для которых идентификаторы проставляются после вставки
        /** @var array<string, array<int, object>> $postInsertIdEntities */
        $postInsertIdEntities = [];
        // Мапа с полями сущности и колонками идентификаторов для таблиц, для которых проставляются они после вставки
        /** @var array<string, string[]> $idColumnsMap */
        $idColumnsMap = [];
        // Количества полей в сущностях (таблица - количество) для наиболее эффективной вставки
        /** @var array<string, int> $entityFieldCounts */
        $entityFieldCounts = [];
        foreach ($entities as $k => &$entity) {
            $metadata = $em->getClassMetadata($entity::class);
            $tableName = $metadata->getTableName();
            // Если есть таблица для замены, то используем ее
            if (isset($replaceTables[$tableName])) {
                $tableName = $replaceTables[$tableName];
            }

            $idColumnsMap[$tableName] ??= $metadata->getIdentifierColumnNames();

            $rowData = [];
            $idGen = $metadata->idGenerator;
            if ($generateId) {
                if ($idGen->isPostInsertGenerator()) {
                    $postInsertIdEntities[$tableName][$k] = $entity;
                } else {
                    $idValue = $idGen->generateId($em, $entity);

                    if (!$idGen instanceof AssignedGenerator) {
                        $idValue = [
                            $metadata->getSingleIdentifierFieldName() => $conn->convertToPHPValue(
                                $idValue,
                                $metadata->getTypeOfField($metadata->getSingleIdentifierFieldName()),
                            ),
                        ];
                        $metadata->setIdentifierValues($entity, $idValue);
                    }
                }
            }

            $entityFieldCounts[$tableName] ??= 0;
            $fieldNames = $metadata->getFieldNames();
            $fieldsCount = 0;
            foreach ($fieldNames as $fieldName) {
                // Пропускаем автогенирируемые во время вставки идентификаторы
                if ($generateId && $metadata->isIdentifier($fieldName) && $idGen->isPostInsertGenerator()) {
                    continue;
                }
                $rowData[$metadata->getColumnName($fieldName)] = $conn->convertToDatabaseValue(
                    $metadata->getFieldValue($entity, $fieldName),
                    $metadata->getTypeOfField($fieldName),
                );
                $fieldsCount++;
            }
            $entityFieldCounts[$tableName] = $fieldsCount;
            $inserts[$tableName][$k] = $rowData;
        }

        // Выполнение SQL-запроса
        $conn->beginTransaction();
        $ids = [];
        try {
            foreach ($inserts as $tableName => $rows) {
                // Разбиваем строки на чанки, чтобы не улететь за лимит в 65535 параметров
                // Сохраняем ключи чтобы потом можно было связать строку из вставки с сущностью, если надо будет проставлять идентификаторы
                $chunks = array_chunk(
                    $rows,
                    floor(65535 / ($entityFieldCounts[$tableName] ?? 100)),
                    true,
                );
                foreach ($chunks as $chunk) {
                    // Колонки ключей
                    $columns = $idColumnsMap[$tableName];
                    $query = Query::insert()
                        ->into($tableName)
                        ->addMultipleValues($chunk)
                        ->setEndOfQuery(' RETURNING "' . implode('", "', $columns) . '"');
                    $result = $this->executeQuery($query);

                    // Первичные ключи для связывания
                    $allIds = $result->fetchAllAssociative();

                    $ids[$tableName] = array_merge($ids[$tableName] ?? [], $allIds);

                    // Если есть сущности, для которых идентификаторы проставляются после вставки, то проставляем им идентификаторы
                    if ($generateId && !empty($postInsertIdEntities[$tableName])) {

                        $entities = &$postInsertIdEntities[$tableName];
                        $metadata = $em->getClassMetadata($entities[array_key_first($entities)]::class);
                        $idsKey = 0;
                        foreach (array_keys($chunk) as $k) {
                            $idValues = $allIds[$idsKey];
                            // Удаляем ссылку на предыдущую сущность
                            unset($entity);
                            $entity = &$entities[$k];

                            array_walk(
                                $idValues,
                                function (mixed &$id, string $column) use (&$conn, &$metadata) {
                                    $id = $conn->convertToPHPValue(
                                        $id,
                                        $metadata->getTypeOfField($metadata->getFieldName($column)),
                                    );
                                },
                            );

                            $metadata->setIdentifierValues(
                                $entity,
                                array_combine(
                                    array_map(
                                        $metadata->getFieldName(...),
                                        $columns,
                                    ),
                                    $idValues,
                                ),
                            );
                            $idsKey++;
                        }
                    }
                }
            }

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }

        return $ids;
    }

    public function updateMulti(array $entities): int
    {
        if ($entities === []) {
            return 0;
        }

        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $conn->beginTransaction();

        try {
            // Находим все таблицы, в которых будет делаться обновление
            // Сразу создаем временные таблицы
            // Также запоминаем метаданные сущностей для таблиц
            $tables = [];
            $metadataForTables = [];
            foreach ($entities as $entity) {
                $metadata = $em->getClassMetadata($entity::class);
                $table = $metadata->getTableName();
                if (!isset($tables[$table])) {
                    $tables[$table] = $this->createTemporaryTable($table);
                    $metadataForTables[$table] = $metadata;
                }
            }

            $this->createMulti($entities, $tables, false);

            // Выполняем обновление
            $updatesCount = 0;
            foreach ($tables as $table => $tempTable) {
                $metadata = $metadataForTables[$table];

                $idColumns = $metadata->getIdentifierColumnNames();
                $notIdColumns = array_diff($metadata->getColumnNames(), $idColumns);

                // Создаем массив колонок, которые надо установить
                $sets = [];
                foreach ($notIdColumns as $column) {
                    $sets[] = "$column = o.$column";
                }
                $sets = implode(', ', $sets);

                // Условие для джойна таблиц
                $where = [];
                foreach ($idColumns as $column) {
                    $where[] = "$table.$column = o.$column";
                }
                $where = implode(' AND ', $where);

                // Обновляем данные
                $updateSql = <<<SQL
                UPDATE $table SET
                $sets FROM $tempTable o
                WHERE $where
                SQL;

                $result = $conn->executeQuery($updateSql);
                $updatesCount += $result->rowCount();

                $this->dropTemporaryTable($tempTable);
            }

            $conn->commit();

            return $updatesCount;
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Создание временной таблицы
     *
     * @param string $originalTable
     *
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    private function createTemporaryTable(string $originalTable): string
    {
        $originalTable = trim($originalTable, '"');
        $tempName = "temp_{$originalTable}_" . time();
        $sql = "CREATE TABLE \"$tempName\" AS SELECT * FROM \"$originalTable\" WHERE FALSE;";
        $this->getEntityManager()->getConnection()->executeQuery($sql);
        return $tempName;
    }

    private function dropTemporaryTable(string $tempTable): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            "DROP TABLE $tempTable",
        );
    }

    /**
     * @template T of object
     * @param SelectQuery $query
     * @param class-string<T>|null $entityClassName
     * @param string[]|null $relations
     *
     * @return ($entityClassName is null ? array : (T&object)|null)
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneByQuery(SelectQuery $query, string|null $entityClassName = null, array|null $relations = null): mixed
    {
        if ($entityClassName) {
            if ($relations) {
                return $this->findWithRelations($query, $entityClassName, $relations, true);
            }
            return $this->getEmNativeQuery($entityClassName, $query)->getOneOrNullResult();
        } else {
            return $this->executeQuery($query)->fetchAssociative() ?: null;
        }
    }

    /**
     * @template T of object
     * @param SelectQuery $query
     * @param class-string<T>|null $entityClassName
     * @param string[]|null $relations
     *
     * @return ($entityClassName is null ? array : (T&object)[])
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllByQuery(SelectQuery $query, string|null $entityClassName = null, array|null $relations = null): array
    {
        if ($entityClassName) {
            if ($relations) {
                return $this->findWithRelations($query, $entityClassName, $relations);
            }
            return $this->getEmNativeQuery($entityClassName, $query)->getResult();
        }

        return $this->executeQuery($query)->fetchAllAssociative();
    }

    /**
     * @param SelectQuery $query
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findColumnByQuery(SelectQuery $query): array
    {
        return $this->executeQuery($query)->fetchFirstColumn();
    }

    /**
     * @template T of object
     * @param SelectQuery $query
     * @param class-string<T>|null $entityClassName
     * @param string[]|null $relations
     * @param DataLimitInterface|null $limit
     * @param DataSortInterface|null $sort
     *
     * @return ($entityClassName is null ? DataProviderInterface<array> : DataProviderInterface<T>)
     * @throws \Doctrine\DBAL\Exception
     */
    public function findWithProvider(
        SelectQuery $query,
        string|null $entityClassName = null,
        array|null $relations = null,
        DataLimitInterface|null $limit = null,
        DataSortInterface|null $sort = null,
    ): DataProviderInterface {
        $limit ??= new LimitOffset(null, 0);
        $sort ??= new DataSort([]);

        $items = [];
        $total = intval(
            $this->findOneByQuery(
                Query::select()
                    ->select(['count' => new Expr('count(*)')])
                    ->from(['t' => (clone $query)->limit(null)->offset(null)]),
            )['count'] ?? 0,
        );

        if ($total > 0) {
            $clonedQuery = (clone $query)->limit($limit->getLimit())->offset($limit->getOffset());
            if ($sort->getSortColumns()) {
                $clonedQuery
                    ->orderBy(
                        array_merge(
                            ...array_map(
                            fn(SortColumnInterface $column) => [$column->getColumn() => $column->getSort()],
                            $sort->getSortColumns(),
                        ),
                        ),
                    );
            }

            $items = $this->findAllByQuery(
                $clonedQuery,
                $entityClassName,
                $relations,
            );
        }

        return new ArrayDataProvider(
            items: $items,
            total: $total,
            limit: $limit,
            sort: $sort,
        );
    }

    /**
     * @template T of object
     * @param SelectQuery $query
     * @param class-string<T>|null $entityClassName
     * @param string[]|null $relations
     * @param DataLimitInterface|null $limit
     * @param DataSortInterface|null $sort
     * @param int $batchSize
     *
     * @return ($entityClassName is null ? DataProviderInterface<array> : DataProviderInterface<T>)
     * @throws \Doctrine\DBAL\Exception
     */
    public function findWithLazyBatchedProvider(
        SelectQuery $query,
        string|null $entityClassName = null,
        array|null $relations = null,
        DataLimitInterface|null $limit = null,
        DataSortInterface|null $sort = null,
        int $batchSize = 500,
    ): DataProviderInterface {
        $limit ??= new LimitOffset(null, 0);
        $sort ??= new DataSort([]);

        $total = intval(
            $this->findScalarByQuery(
                Query::select()
                    ->select(['count' => new Expr('count(*)')])
                    ->from(['t' => (clone $query)->limit(null)->offset(null)]),
            ) ?: 0,
        );

        if ($total > 0) {
            $fetcher = function (DataLimitInterface $limit) use (&$query, &$sort, &$entityClassName, &$relations) {
                $clonedQuery = (clone $query)->limit($limit->getLimit())->offset($limit->getOffset());
                if ($sort->getSortColumns()) {
                    $clonedQuery
                        ->orderBy(
                            array_merge(
                                ...array_map(
                                fn(SortColumnInterface $column) => [$column->getColumn() => $column->getSort()],
                                $sort->getSortColumns(),
                            ),
                            ),
                        );
                }

                return new ArrayIterator(
                    $this->findAllByQuery(
                        $clonedQuery,
                        $entityClassName,
                        $relations,
                    ),
                );
            };
            return new LazyBatchedDataProvider(
                $fetcher,
                $batchSize,
                max(0, min($total - $limit->getOffset(), $limit->getLimit() ?? $total)),
                $total,
                $limit,
                $sort,
            );
        }

        return new ArrayDataProvider([], 0, $limit, $sort);
    }

    /**
     * @param QueryInterface $query
     *
     * @return Result
     * @throws \Doctrine\DBAL\Exception
     */
    public function executeQuery(QueryInterface $query): Result
    {
        $qb = $query->getQueryBuilder()->setDialect(DialectSQL::POSTGRESQL);
        $expr = $qb->build();

        $connection = $this->getEntityManager()->getConnection();
        $params = $expr->getParams();
        $preparedParams = [];
        $types = [];
        array_walk(
            $params,
            function ($val, $k) use (&$preparedParams, &$types) {
                $k = ltrim($k, ':');
                $preparedParams[$k] = $val;
                $types[$k] = match (true) {
                    is_bool($val) => ParameterType::BOOLEAN,
                    is_null($val) => ParameterType::NULL,
                    is_integer($val) => ParameterType::INTEGER,
                    default => ParameterType::STRING,
                };
            },
        );
        return $connection->executeQuery(
            $expr->getExpression(),
            $preparedParams,
            $types,
        );
    }

    /**
     * Поиск с учетом релейшенов
     *
     * @template T of object
     *
     * @param SelectQuery $query
     * @param class-string<T> $class
     * @param string[] $relations
     * @param bool $one
     *
     * @return ($one is false ? (T&object)[] : (T&object)|null)
     * @throws \Doctrine\DBAL\Exception
     */
    protected function findWithRelations(SelectQuery $query, string $class, array $relations, bool $one = false): array|object|null
    {
        $em = $this->getEntityManager();
        $metadata = $em->getClassMetadata($class);

        $idColumns = $metadata->getIdentifierColumnNames();

        $idQuery = Query::select()
            ->from(['s' => $query])
            ->select($idColumns);

        // Получаем id сущностей из запроса (ВАЖНО, названия полей в id это названия колонок, а не свойств сущности)
        $ids = $this->findAllByQuery($idQuery);

        if ($ids === []) {
            return $one === true ? null : [];
        }

        // Формируем DQL-запрос для подгрузки ассоциаций с учетом сложных ключей
        $qb = $em->createQueryBuilder()->select('e')->from("$class", 'e');

        // Добавляем условия для каждого идентификатора
        $orX = $qb->expr()->orX();
        $i = 0;
        foreach ($ids as $id) {
            $andX = $qb->expr()->andX();
            foreach ($id as $column => $value) {
                $field = $metadata->getFieldName($column);
                $paramName = "prqb_$i";
                $i++;
                $andX->add($qb->expr()->eq("e.{$field}", ":{$paramName}"));
                $qb->setParameter($paramName, $value);
            }
            $orX->add($andX);
        }
        $qb->where($orX);

        // Добавляем забытые и удаляем ненужные релейшены
        $relations = $this->normalizeRelations($relations);

        // Делаем хеши для каждого релейшена
        $relationsHashes = $this->hashRelations($relations);

        // Добавляем необходимые JOIN FETCH для ассоциаций
        foreach ($relations as $relation) {
            // Если релейшен вложенный, то нужна особая обработка
            if (str_contains($relation, '.')) {
                preg_match('/(.*?)\.([^.]*)$/ui', $relation, $matches);
                $parentRel = $matches[1];
                $rel = $matches[2];
                $parentHash = $relationsHashes[$parentRel];
                $relHash = $relationsHashes[$relation];
                $qb
                    ->leftJoin("$parentHash.$rel", $relHash)
                    ->addSelect($relHash);
            } else {
                $hash = $relationsHashes[$relation];
                $qb
                    ->leftJoin("e.$relation", $hash)
                    ->addSelect($hash);
            }
        }

        if ($one) {
            return $qb->getQuery()->getOneOrNullResult();
        }
        // Выполняем запрос и получаем сущности с подгруженными ассоциациями
        $result = $qb->getQuery()->getResult();

        // Теперь надо отсортировать полученные сущности в соответствие с порядком строк в ids
        // Сначала находим номер для каждого идентификатора сущностей по $ids, чтобы отсортировать $result
        $idsOrder = array_flip(
            array_map(
                $em->getUnitOfWork()::getIdHashByIdentifier(...),
                $ids,
            ),
        );

        // Находим текущий порядок сущностей в $result
        $result = HArray::index(
            $result,
            fn(object $o) => $idsOrder[$em->getUnitOfWork()->getIdHashByEntity($o)],
        );

        // Сортируем в соответствие с порядком $ids
        ksort($result, SORT_ASC);

        return $result;
    }

    /**
     * Нормализуем релейшены. Удаляем лишние и добавляем забытые
     *
     * @param string[] $relations
     *
     * @return string[]
     */
    private function normalizeRelations(array $relations): array
    {
        $result = [];
        foreach ($relations as $relation) {
            $nested = explode('.', $relation);
            $nestLevel = [];
            foreach ($nested as $nestRel) {
                $nestLevel[] = $nestRel;
                $rel = implode('.', $nestLevel);
                $result[$rel] = $rel;
            }
        }
        return $result;
    }

    /**
     * Хешируем релейшены для дальнейшей работы с ними
     *
     * @param string[] $relations
     *
     * @return array<string, string>
     */
    private function hashRelations(array $relations): array
    {
        $sameCounts = [];
        $relationsHashes = [];
        foreach ($relations as $relation) {
            $hash = 'rel_' . str_replace('.', '_', $relation);
            // Если получился хеш, который уже есть, то дописываем цифру в конец релейшена
            // Это работает, потому что мы заранее убрали неуникальные значения из списка релейшенов
            $realHash = $hash;
            while (array_key_exists($hash, $sameCounts)) {
                $hash = $realHash . ($sameCounts[$hash]++);
            }
            $realHash = $hash;
            $sameCounts[$realHash] = ($sameCounts[$realHash] ?? 0) + 1;
            $relationsHashes[$relation] = $realHash;
        }
        return $relationsHashes;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getEmNativeQuery(string $entityClassName, SelectQuery $query): NativeQuery
    {
        $qb = $query->getQueryBuilder()->setDialect(DialectSQL::POSTGRESQL);
        $expr = $qb->build();

        $rsm = new ResultSetMapping();

        $rsm->addEntityResult($entityClassName, $entityClassName);
        $mapping = $this->getEntityManager()->getClassMetadata($entityClassName);
        foreach ($mapping->fieldMappings as $fieldMapping) {
            $rsm->addFieldResult($entityClassName, $fieldMapping->columnName, $fieldMapping->fieldName);
        }

        $emQuery = $this->getEntityManager()->createNativeQuery($expr->getExpression(), $rsm);
        $emQuery->setParameters($expr->getParams());
        $emQuery->setHint(DQuery::HINT_REFRESH, true);

        return $emQuery;
    }

    /**
     * @param SelectQuery $q
     *
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    protected function queryCount(SelectQuery $q): int
    {
        return $this->findScalarByQuery(
            Query::select()
                ->select(new Expr('count(*)'))
                ->from(['t' => $q]),
        );
    }

    /**
     * @param SelectQuery $q
     *
     * @return bool|int|float|string|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findScalarByQuery(SelectQuery $q): null|bool|int|float|string
    {
        return $this->executeQuery($q)->fetchOne();
    }
}
