<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Infrastructure\Messenger\Message\TelegramLogMessage;
use CurlHandle;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramLogHandler
{
    public const string BOT_API = 'https://api.telegram.org/bot';

    public function __invoke(TelegramLogMessage $message): void
    {
        $this->send($message);
    }

    private function send(TelegramLogMessage $message): void
    {
        if ($message->getMessage() === '') {
            return;
        }

        $ch = curl_init();
        $url = self::BOT_API . $message->getApiKey() . '/SendMessage';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $params = [
            'text' => $message->getMessage(),
            'chat_id' => $message->getChannel(),
            'parse_mode' => $message->getParseMode(),
            'disable_web_page_preview' => $message->getDisableWebPagePreview(),
            'disable_notification' => $message->getDisableNotification(),
        ];
        if ($message->getTopic() !== null) {
            $params['message_thread_id'] = $message->getTopic();
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $result = $this->execute($ch);
        curl_close($ch);
        if (!is_string($result)) {
            throw new RuntimeException('Telegram API error. Description: No response');
        }
        $result = json_decode($result, true);

        if ($result['ok'] === false) {
            throw new RuntimeException('Telegram API error. Description: ' . $result['description']);
        }
    }

    private function execute(CurlHandle $ch): bool|string
    {
        $curlResponse = curl_exec($ch);
        if ($curlResponse === false) {
            $curlErrno = curl_errno($ch);
            $curlError = curl_error($ch);

            throw new RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
        }

        return $curlResponse;
    }
}
