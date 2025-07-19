<?php

namespace App\Infrastructure\Service\Logger;

use App\Infrastructure\Service\Logger\TextEntityProcessor\Config\MarkdownV2EntitiesConfig;
use App\Infrastructure\Service\Logger\TextEntityProcessor\Config\PreMarkdownV2EntitiesConfig;
use App\Infrastructure\Service\Logger\TextEntityProcessor\TextEntitiesProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Utils;
use Symfony\Component\HttpFoundation\RequestStack;

class TelegramFormatter extends LineFormatter
{
    private const MAX_MESSAGE_LENGTH = 4096;

    public ?RequestStack $requestStack;
    public const SIMPLE_FORMAT = "%icon% *%url%*\n%ip%\n```text\n%message%\n%context.exception%```";

    public function __construct(
        RequestStack $requestStack,
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
        bool $includeStacktraces = false,
    ) {
        $this->requestStack = $requestStack;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }

    public function format(LogRecord $record): string
    {
        $output = parent::format($record);

        return $this->handleMessageLength($output);
    }

    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        $request = $this->requestStack->getCurrentRequest();
        $url = $request?->getUri();
        $textProcessor = new TextEntitiesProcessor();
        if (isset($record->context['command'])) {
            $url = sprintf(
                "php bin/console %s",
                $record->context['command'],
            );
        }

        if (isset($normalized['message'])) {
            $normalized['message'] = $textProcessor->putTelegramEntitiesIntoText(
                $normalized['message'],
                [],
                new PreMarkdownV2EntitiesConfig(),
            );
        }

        $extra = [
            'icon' => $this->getIcon($record->level),
            'url' => $url,
            'command' => $url,
            'ip' => $request?->getClientIp() ?: '',
        ];

        foreach ($extra as &$val) {
            $val = $textProcessor->putTelegramEntitiesIntoText(
                (string)$val,
                [],
                new MarkdownV2EntitiesConfig(),
            );
        }

        return array_merge($normalized, $extra);
    }

    private function getIcon(Level $level): string
    {
        return match ($level) {
            Level::Error, Level::Critical, Level::Alert, Level::Emergency => '☠️',
            Level::Warning => '⚠️',
            Level::Info => 'ℹ️',
            Level::Debug => '🐛',
            default => '',
        };
    }

    private function handleMessageLength(string $message): string
    {
        $truncatedMarker = " (...truncated)\n```";
        if (strlen($message) > self::MAX_MESSAGE_LENGTH) {
            return Utils::substr($message, 0, self::MAX_MESSAGE_LENGTH - strlen($truncatedMarker)) . $truncatedMarker;
        }

        return $message;
    }
}
