<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor\Config;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Dto\MessageEntity;

class HtmlEntitiesConfig implements EntitiesConfigInterface
{
    public function getAvailableEntityTypes(): array
    {
        return ['bold', 'italic', 'underline', 'strikethrough', 'code', 'pre', 'text_link', 'url', 'blockquote'];
    }

    public function getEntityStartTag(MessageEntity $entity): string
    {
        return match ($entity->getType()) {
            'bold' => '<b>',
            'italic' => '<i>',
            'underline' => '<u>',
            'strikethrough' => '<s>',
            'code' => '<code>',
            'pre' => $entity->getLanguage() !== null ? '<pre><code class="language-' . $entity->getLanguage() . '">' : '<pre>',
            'text_link' => '<a href="' . $entity->getUrl() . '">',
            'url' => '',
            'blockquote' => '<blockquote>',
            default => '',
        };
    }

    public function getEntityEndTag(MessageEntity $entity): string
    {
        return match ($entity->getType()) {
            'bold' => '</b>',
            'italic' => '</i>',
            'underline' => '</u>',
            'strikethrough' => '</s>',
            'code' => '</code>',
            'pre' => $entity->getLanguage() !== null ? '</code></pre>' : '</pre>',
            'text_link' => '</a>',
            'url' => '',
            'blockquote' => '</blockuote>',
            default => '',
        };
    }

    public function canEntityContainsSubEntities(MessageEntity $entity): bool
    {
        return match ($entity->getType()) {
            'bold' => true,
            'italic' => true,
            'underline' => true,
            'strikethrough' => true,
            'code' => false,
            'pre' => false,
            'text_link' => true,
            'url' => false,
            'blockquote' => true,
            default => false,
        };
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
        return [];
    }
}
