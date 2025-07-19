<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor\Config;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Dto\MessageEntity;

class MarkdownV2EntitiesConfig implements EntitiesConfigInterface
{
    public function getAvailableEntityTypes(): array
    {
        return ['bold', 'italic', 'underline', 'strikethrough', 'code', 'pre', 'text_link', 'blockquote', 'hashtag'];
    }

    public function getEntityStartTag(MessageEntity $entity): string
    {
        return match ($entity->getType()) {
            'bold' => '*',
            'italic' => '_',
            'underline' => '__',
            'strikethrough' => '~',
            'code' => '`',
            'pre' => $entity->getLanguage() !== null ? "```{$entity->getLanguage()}\n" : "```\n",
            'text_link' => '[',
            'blockquote' => '',
            'hashtag' => '',
            default => '',
        };
    }

    public function getEntityEndTag(MessageEntity $entity): string
    {
        return match ($entity->getType()) {
            'bold' => '*',
            'italic' => '_',
            'underline' => '__',
            'strikethrough' => '~',
            'code' => '`',
            'pre' => '```',
            'text_link' => "]({$entity->getUrl()})",
            'blockquote' => '',
            'hashtag' => '',
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
            default => false,
        };
    }

    public function isEntityMultiline(MessageEntity $entity): bool
    {
        return match ($entity->getType()) {
            'blockquote' => true,
            default => false,
        };
    }

    public function getMultilineEntityLineTag(MessageEntity $entity): string
    {
        return match ($entity->getType()) {
            'blockquote' => '>',
            default => '',
        };
    }

    public function getEscapedChars(MessageEntity|null $entity = null): array
    {
        return match ($entity?->getType()) {
            'bold' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'italic' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'underline' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'strikethrough' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'blockquote' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'hashtag' => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            'pre' => ['`'],
            'code' => ['`'],
            'text_link' => [')'],
            default => ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '.', '!', '\\', '{', '}'],
        };
    }
}
