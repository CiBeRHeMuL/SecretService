<?php

namespace App\Infrastructure\Service\Logger\Handler;

use App\Infrastructure\Messenger\Message\TelegramLogMessage;
use Monolog\Level;
use Symfony\Component\Messenger\MessageBusInterface;

class TelegramBotHandler extends \Monolog\Handler\TelegramBotHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private string $apiKey,
        private string $channel,
        private string|null $parseMode = null,
        private bool|null $disableWebPagePreview = null,
        private bool|null $disableNotification = null,
        private int|null $topic = null,
        $level = Level::Debug,
        bool $bubble = true,
        bool $splitLongMessages = false,
        bool $delayBetweenMessages = false,
    ) {
        parent::__construct(
            $apiKey,
            $channel,
            $level,
            $bubble,
            $parseMode,
            $disableWebPagePreview,
            $disableNotification,
            $splitLongMessages,
            $delayBetweenMessages,
            $topic,
        );
    }

    protected function sendCurl(string $message): void
    {
        $this->messageBus->dispatch(
            new TelegramLogMessage(
                $this->apiKey,
                $this->channel,
                $message,
                $this->parseMode,
                $this->disableWebPagePreview,
                $this->disableNotification,
                $this->topic,
            ),
        );
    }
}
