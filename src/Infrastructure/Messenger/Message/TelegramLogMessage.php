<?php

namespace App\Infrastructure\Messenger\Message;

use SensitiveParameter;

readonly class TelegramLogMessage
{
    public function __construct(
        #[SensitiveParameter]
        private string $apiKey,
        #[SensitiveParameter]
        private string $channel,
        private string $message,
        private string|null $parseMode = null,
        private bool|null $disableWebPagePreview = null,
        private bool|null $disableNotification = null,
        private int|null $topic = null,
    ) {
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getParseMode(): ?string
    {
        return $this->parseMode;
    }

    public function getDisableWebPagePreview(): ?bool
    {
        return $this->disableWebPagePreview;
    }

    public function getDisableNotification(): ?bool
    {
        return $this->disableNotification;
    }

    public function getTopic(): ?int
    {
        return $this->topic;
    }
}
