<?php

namespace App\Infrastructure\Service\Logger\TextEntityProcessor\Dto;

class MessageEntity
{
    public function __construct(
        public int $offset,
        public int $length,
        public string $type,
        public string|null $url,
        public string|null $language,
    ) {
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): MessageEntity
    {
        $this->offset = $offset;
        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): MessageEntity
    {
        $this->length = $length;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): MessageEntity
    {
        $this->type = $type;
        return $this;
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }

    public function setUrl(string|null $url): MessageEntity
    {
        $this->url = $url;
        return $this;
    }

    public function getLanguage(): string|null
    {
        return $this->language;
    }

    public function setLanguage(string|null $language): MessageEntity
    {
        $this->language = $language;
        return $this;
    }
}
