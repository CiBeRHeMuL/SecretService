<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor\Config;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Dto\MessageEntity;

class PreMarkdownV2EntitiesConfig implements EntitiesConfigInterface
{
    public function getAvailableEntityTypes(): array
    {
        return [];
    }

    public function getEntityStartTag(MessageEntity $entity): string
    {
        return '';
    }

    public function getEntityEndTag(MessageEntity $entity): string
    {
        return '';
    }

    public function canEntityContainsSubEntities(MessageEntity $entity): bool
    {
        return false;
    }

    public function isEntityMultiline(MessageEntity $entity): bool
    {
        return false;
    }

    public function getMultilineEntityLineTag(MessageEntity $entity): string
    {
        return '';
    }

    public function getEscapedChars(MessageEntity|null $entity = null): array
    {
        return ['`', '\\'];
    }
}
