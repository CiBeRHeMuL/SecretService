<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Config\EntitiesConfigInterface;
use App\Infrastructure\Service\Logger\TextEntityProcessor\Dto\MessageEntity;

class TextEntitiesProcessor
{
    private array $escapedChars = [];

    /**
     * @param string $text
     * @param MessageEntity[] $entities
     * @param EntitiesConfigInterface $entitiesConfig
     *
     * @return string
     */
    public function putTelegramEntitiesIntoText(string $text, array $entities, EntitiesConfigInterface $entitiesConfig): string
    {
        /**
         * Удаляем все некорректные сущности
         */
        $entities = array_filter(
            $entities,
            fn($e) => $e instanceof MessageEntity
                && in_array($e->getType(), $entitiesConfig->getAvailableEntityTypes())
                && $e->getOffset() >= 0
                && $e->getLength() > 0
                && $e->getOffset() + $e->getLength() <= $this->strlen($text),
        );
        if ($entities) {
            /**
             * Сортируем сущности в порядке следования в тексте (с учетом вложенности)
             */
            uasort(
                $entities,
                function (MessageEntity $l, MessageEntity $r) {
                    if ($l->getOffset() === $r->getOffset()) {
                        return -($l->getLength() <=> $r->getLength());
                    } else {
                        return $l->getOffset() <=> $r->getOffset();
                    }
                },
            );
            $entities = array_values($entities);

            $currentTextPosition = 0;
            $entityId = 0;
            $result = '';
            $oldEscapedChars = $this->escapedChars;
            $this->escapedChars = $this->escapedChars + $entitiesConfig->getEscapedChars();
            while ($entityId < count($entities)) {
                $entity = $entities[$entityId];
                /**
                 * Если до сущности есть текст без сущностей, то присоединяем к результату этот текст
                 */
                if ($currentTextPosition < $entity->getOffset()) {
                    $subText = $this->substr($text, $currentTextPosition, $entity->getOffset() - $currentTextPosition);
                    if ($this->escapedChars) {
                        $subText = preg_replace('/(?<!\\\)(\\' . implode('|\\', $this->escapedChars) . ')/i', '\\\$1', $subText);
                    }
                    $result .= $subText;
                    $currentTextPosition = $entity->getOffset();
                }
                $this->escapedChars = $this->escapedChars + $entitiesConfig->getEscapedChars($entity);

                /**
                 * Получаем открывающий и закрывающий теги для текущей сущности
                 */
                $startTag = $entitiesConfig->getEntityStartTag($entity);
                $endTag = $entitiesConfig->getEntityEndTag($entity);
                /**
                 * Открываем текущую сущность
                 */
                $result .= $startTag;

                /**
                 * Если сущность может содержать вложенные теги, то обрабатываем вложенные теги рекурсивно
                 * Иначе скипаем все возможные вложенные теги
                 *
                 * Для этого находим все нужные теги и сдвигаем указатель массива сущностей на нужное количество шагов
                 */
                $subEntities = [];
                $currentSubEntityId = $entityId + 1;
                while ($currentSubEntityId < count($entities)) {
                    $subEntity = $entities[$currentSubEntityId];
                    if ($subEntity->getOffset() < $entity->getOffset() + $entity->getLength()) {
                        $subEntity->setOffset($subEntity->getOffset() - $entity->getOffset());
                        $subEntities[] = $subEntity;
                    } else {
                        break;
                    }
                    $currentSubEntityId++;
                }
                /**
                 * Если есть вложенные теги и сущность может содержать вложенные теги, то подставляем их
                 * Иначе просто конкатенируем результат и текст внутри текущей сущности
                 */
                $preparedSubText = '';
                if ($entitiesConfig->canEntityContainsSubEntities($entity) && $subEntities) {
                    $preparedSubText = $this->putTelegramEntitiesIntoText(
                        $this->substr($text, $entity->getOffset(), $entity->getLength()),
                        $subEntities,
                        $entitiesConfig,
                    );
                    /**
                     * Вычисляем сущности с самым первым открывающим тегом и с самым последним закрывающим
                     * @var MessageEntity $startSubEntity
                     * @var MessageEntity $endSubEntity
                     */
                    $startSubEntity = reset($subEntities);
                    $endSubEntity = array_reduce(
                        $subEntities,
                        function (MessageEntity $maxEntity, MessageEntity $subEntity) {
                            if ($maxEntity->getOffset() + $maxEntity->getLength() < $subEntity->getOffset() + $subEntity->getLength()) {
                                return $subEntity;
                            } else {
                                return $maxEntity;
                            }
                        },
                        $startSubEntity,
                    );
                    $startSubEntityTag = $entitiesConfig->getEntityStartTag($startSubEntity);
                    $endSubEntityTag = $entitiesConfig->getEntityStartTag($endSubEntity);
                    /**
                     * Если текст начинается с сущности, тег которой содержится с текущей,
                     * то необходимо добавить \r для того, чтобы парсеры могли читать разметку
                     */
                    if (
                        $startSubEntity->getOffset() === 0
                        && $startSubEntityTag
                        && (str_contains($startTag, $startSubEntityTag) || str_contains($startSubEntityTag, $startTag))
                    ) {
                        $preparedSubText = "\r$preparedSubText";
                    }
                    /**
                     * Если текст заканчивается сущностью, тег которой содержится с текущей,
                     * то необходимо добавить \r для того, чтобы парсеры могли читать разметку
                     */
                    if (
                        $endSubEntity->getOffset() + $endSubEntity->getLength() === $entity->getLength()
                        && $endSubEntityTag
                        && (str_contains($endTag, $endSubEntityTag) || str_contains($endSubEntityTag, $endTag))
                    ) {
                        $preparedSubText = "$preparedSubText\r";
                    }
                } else {
                    $preparedSubText = $this->substr($text, $entity->getOffset(), $entity->getLength());
                    if ($this->escapedChars) {
                        $preparedSubText = preg_replace('/(?<!\\\)(\\' . implode('|\\', $this->escapedChars) . ')/i', '\\\$1', $preparedSubText);
                    }
                }

                /**
                 * Если сущность многострочная, то подставляем в начало каждой строки строчный открывающий тег
                 */
                if ($entitiesConfig->isEntityMultiline($entity)) {
                    $lineStartTag = $entitiesConfig->getMultilineEntityLineTag($entity);
                    $preparedSubText = $lineStartTag . str_replace("\n", "\n$lineStartTag", $preparedSubText);
                }
                $result .= $preparedSubText;

                $entityId += count($subEntities) + 1;

                /**
                 * Закрываем текущую сущность
                 */
                $result .= $endTag;
                $currentTextPosition += $entity->getLength();
            }
            /**
             * Добавляем оставшийся текст
             */
            if ($currentTextPosition < $this->strlen($text)) {
                $subText = $this->substr($text, $currentTextPosition);
                if ($this->escapedChars) {
                    $subText = preg_replace('/(?<!\\\)(\\' . implode('|\\', $this->escapedChars) . ')/i', '\\\$1', $subText);
                }
                $result .= $subText;
            }
            $this->escapedChars = $oldEscapedChars;
            return $result;
        }
        if ($entitiesConfig->getEscapedChars()) {
            $text = preg_replace(
                '/(?<!\\\)(\\' . implode('|\\', $entitiesConfig->getEscapedChars()) . ')/i',
                '\\\$1',
                $text,
            );
        }
        return $text;
    }

    /**
     * Считаем длину строки согласно документации телеграмма
     * @see https://core.telegram.org/api/entities#computing-entity-length
     *
     * @param string $text
     *
     * @return int
     */
    private function strlen(string $text): int
    {
        $result = 0;
        foreach (str_split($text) as $byte) {
            $result += $this->getByteLength($byte);
        }
        return $result;
    }

    private function substr(string $text, int $offset, int|null $length = null): string
    {
        if ($length === 0) {
            return '';
        }
        $result = '';
        $textLength = 0;
        foreach (str_split($text) as $byte) {
            $textLength += $this->getByteLength($byte);
            if ($textLength > $offset) {
                if ($length !== null) {
                    if ($textLength > $offset + $length) {
                        break;
                    }
                }
                $result .= $byte;
            }
        }
        return $result;
    }

    /**
     * Получаем длину байта согласно документации телеграмма
     * @see https://core.telegram.org/api/entities#computing-entity-length
     *
     * @param string $byte
     *
     * @return int
     */
    private function getByteLength(string $byte): int
    {
        $el = ord($byte);
        if (($el & 0xC0) != 0x80) {
            return ($el >= 0xF0 ? 2 : 1);
        }
        return 0;
    }
}
