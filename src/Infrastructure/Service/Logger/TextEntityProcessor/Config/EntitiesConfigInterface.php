<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor\Config;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Dto\MessageEntity;

interface EntitiesConfigInterface
{
    /**
     * Сущности, которые можно обрабатывать
     * @return string[]
     */
    public function getAvailableEntityTypes(): array;

    /**
     * Открывающий тег сущности
     *
     * @param MessageEntity $entity
     *
     * @return string
     */
    public function getEntityStartTag(MessageEntity $entity): string;

    /**
     * Закрывающий тег сущности
     *
     * @param MessageEntity $entity
     *
     * @return string
     */
    public function getEntityEndTag(MessageEntity $entity): string;

    /**
     * Может ли сущность содержать другие сущности
     *
     * @param MessageEntity $entity
     *
     * @return bool
     */
    public function canEntityContainsSubEntities(MessageEntity $entity): bool;

    /**
     * Является ли сущность многострочной (то есть каждая строка начинается с тега)
     *
     * @param MessageEntity $entity
     *
     * @return bool
     */
    public function isEntityMultiline(MessageEntity $entity): bool;

    /**
     * Открывающий тег для каждой строки многострочной сущности
     *
     * @param MessageEntity $entity
     *
     * @return string
     */
    public function getMultilineEntityLineTag(MessageEntity $entity): string;

    /**
     * Символы, которые надо экранировать
     * @return string[]
     */
    public function getEscapedChars(MessageEntity|null $entity = null): array;
}
