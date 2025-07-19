<?php

namespace App\Domain\Helper;

use DateInterval;

class HDate
{
    public static function calculateInterval(string|float|int $interval): DateInterval
    {
        if (is_int($interval) || is_float($interval) || is_string($interval) && ctype_digit($interval)) {
            $interval = (int)$interval;
            return new DateInterval("PT{$interval}S");
        }

        if (str_starts_with($interval, 'P')) {
            return new DateInterval($interval);
        }

        return DateInterval::createFromDateString($interval);
    }

    /**
     * Отображает интервал в строку вида "год 2 месяца 3 дня 4 часа 5 минут 6 секунд"
     *
     * @param DateInterval $interval
     * @param bool $generative родительный падеж
     * @param bool $withSeconds
     * @param bool $withMicroseconds
     *
     * @return string
     */
    public static function formatInterval(
        DateInterval $interval,
        bool $generative = false,
        bool $withSeconds = false,
        bool $withMicroseconds = false,
    ): string {
        $res = [];
        $interval->y !== 0
        && $res[] = msgfmt_format_message(
            'ru_RU',
            "{y,plural,=0{}one{год}few{# года}many{# лет}other{# лет}}",
            ['y' => $interval->y],
        );
        $interval->m !== 0
        && $res[] = msgfmt_format_message(
            'ru_RU',
            "{m,plural,=0{}one{месяц}few{# месяца}many{# месяцев}other{# месяцев}}",
            ['m' => $interval->m],
        );
        $interval->d !== 0
        && $res[] = msgfmt_format_message(
            'ru_RU',
            "{d,plural,=0{}one{день}few{# дня}many{# дней}other{# дней}}",
            ['d' => $interval->d],
        );
        $interval->h !== 0
        && $res[] = msgfmt_format_message(
            'ru_RU',
            "{h,plural,=0{}one{час}few{# часа}many{# часов}other{# часов}}",
            ['h' => $interval->h],
        );
        if ($generative) {
            $interval->i !== 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{i,plural,=0{}one{минуту}few{# минуты}many{# минут}other{# минут}}",
                ['i' => $interval->i],
            );
            $withSeconds && $interval->s !== 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{s,plural,=0{}one{секунду}few{# секунды}many{# секунд}other{# секунд}}",
                ['s' => $interval->s],
            );
            $withMicroseconds && $interval->f > 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{f,plural,=0{}one{микросекунду}few{# микросекунды}many{# микросекунд}other{# микросекунд}}",
                ['f' => $interval->f],
            );
        } else {
            $interval->i !== 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{i,plural,=0{}one{минута}few{# минуты}many{# минут}other{# минут}}",
                ['i' => $interval->i],
            );
            $withSeconds && $interval->s !== 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{s,plural,=0{}one{секунда}few{# секунды}many{# секунд}other{# секунд}}",
                ['s' => $interval->s],
            );
            $withMicroseconds && $interval->f > 0
            && $res[] = msgfmt_format_message(
                'ru_RU',
                "{f,plural,=0{}one{микросекунда}few{# микросекунды}many{# микросекунд}other{# микросекунд}}",
                ['f' => $interval->f],
            );
        }
        return implode(' ', $res);
    }
}
