import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        interval: {y: Number, m: Number, d: Number, h: Number, i: Number, s: Number},
        whileMessage: String,
        expiredMessage: String,
        whileType: String,
        expiredType: String,
    }

    static targets = ["alert", "timerDisplay", "whileIcon", "expiredIcon"];

    connect() {
        this.remainingInterval = this.intervalValue;
        if (this.isIntervalEmpty(this.remainingInterval)) {
            this.expire();
            return;
        }
        this.updateTimerDisplay();

        this.timerId = setInterval(() => {
            this.remainingInterval = this.subtractSecondsFromInterval(this.remainingInterval, 1);
            this.updateTimerDisplay();

            if (this.isIntervalEmpty(this.remainingInterval)) {
                this.expire();
            }
        }, 1000);
    }

    disconnect() {
        // Очищаем таймер, если компонент будет удален со страницы
        clearInterval(this.timerId);
    }

    updateTimerDisplay() {
        const formattedInterval = this.formatInterval(this.remainingInterval, true, true);
        this.timerDisplayTarget.textContent = `${this.whileMessageValue} ${formattedInterval}`;
        this.whileIconTarget.classList.remove('d-none');
        this.expiredIconTarget.classList.add('d-none');
    }

    expire() {
        clearInterval(this.timerId);
        this.timerDisplayTarget.textContent = this.expiredMessageValue;
        this.alertTarget.classList.remove('alert-' + this.whileTypeValue);
        this.alertTarget.classList.add('alert-' + this.expiredTypeValue);
        this.whileIconTarget.classList.add('d-none');
        this.expiredIconTarget.classList.remove('d-none');
    }

    getPlural(number, one, few, many) {
        const rules = new Intl.PluralRules('ru-RU');
        const category = rules.select(number);
        switch (category) {
            case 'one':
                return one;
            case 'few':
                return few;
            case 'many':
                return many;
            default:
                return many;
        }
    }

    /**
     * Отображает интервал в строку вида "1 год 2 месяца 3 дня 4 часа 5 минут 6 секунд"
     * на основе логики из HDate::formatInterval.
     *
     * @param {object} interval - Объект интервала.
     * @param {number} [interval.y=0] - Годы.
     * @param {number} [interval.m=0] - Месяцы.
     * @param {number} [interval.d=0] - Дни.
     * @param {number} [interval.h=0] - Часы.
     * @param {number} [interval.i=0] - Минуты.
     * @param {number} [interval.s=0] - Секунды.
     * @param {number} [interval.f=0] - Микросекунды.
     * @param {boolean} [generative=false] - Использовать родительный падеж для минут/секунд.
     * @param {boolean} [withSeconds=false] - Включать ли секунды в вывод.
     * @returns {string} - Отформатированная строка.
     */
    formatInterval(interval, generative = false, withSeconds = false) {
        const res = [];
        const {y = 0, m = 0, d = 0, h = 0, i = 0, s = 0} = interval;

        if (y > 0) res.push(`${y} ${this.getPlural(y, 'год', 'года', 'лет')}`);
        if (m > 0) res.push(`${m} ${this.getPlural(m, 'месяц', 'месяца', 'месяцев')}`);
        if (d > 0) res.push(`${d} ${this.getPlural(d, 'день', 'дня', 'дней')}`);
        if (h > 0) res.push(`${h} ${this.getPlural(h, 'час', 'часа', 'часов')}`);
        if (i > 0) {
            const forms = generative ? ['минуту', 'минуты', 'минут'] : ['минута', 'минуты', 'минут'];
            res.push(`${i} ${this.getPlural(i, forms[0], forms[1], forms[2])}`);
        }
        if (withSeconds && s > 0) {
            const forms = generative ? ['секунду', 'секунды', 'секунд'] : ['секунда', 'секунды', 'секунд'];
            res.push(`${s} ${this.getPlural(s, forms[0], forms[1], forms[2])}`);
        }
        return res.join(' ');
    }

    /**
     * Вычитает указанное количество секунд из объекта-интервала.
     *
     * Эта функция конвертирует интервал в общее количество секунд, производит
     * вычитание и затем преобразует результат обратно в формат интервала.
     *
     * ВНИМАНИЕ: Для преобразования лет и месяцев в секунды используются средние
     * значения (365 дней в году, 30 дней в месяце), так как точное
     * количество дней зависит от конкретных дат, которых здесь нет.
     *
     * @param {object} interval - Исходный интервал времени.
     * @param {number} [interval.y=0] - Годы.
     * @param {number} [interval.m=0] - Месяцы.
     * @param {number} [interval.d=0] - Дни.
     * @param {number} [interval.h=0] - Часы.
     * @param {number} [interval.i=0] - Минуты.
     * @param {number} [interval.s=0] - Секунды.
     * @param {number} [interval.f=0] - Микросекунды (не участвуют в расчете секунд, но сохраняются).
     * @param {number} secondsToSubtract - Количество секунд для вычитания.
     * @returns {object} Новый объект интервала с результатом вычитания.
     */
    subtractSecondsFromInterval(interval, secondsToSubtract) {
        const SECONDS_IN_MINUTE = 60;
        const SECONDS_IN_HOUR = 3600;
        const SECONDS_IN_DAY = 86400;
        const SECONDS_IN_MONTH = 2592000; // ~30 дней
        const SECONDS_IN_YEAR = 31536000; // ~365 дней

        const {y = 0, m = 0, d = 0, h = 0, i = 0, s = 0} = interval;
        let totalSeconds = (y * SECONDS_IN_YEAR) + (m * SECONDS_IN_MONTH) + (d * SECONDS_IN_DAY) + (h * SECONDS_IN_HOUR) + (i * SECONDS_IN_MINUTE) + s;
        let newTotalSeconds = totalSeconds - secondsToSubtract;
        if (newTotalSeconds < 0) newTotalSeconds = 0;

        const newInterval = {};
        let remainder = newTotalSeconds;
        newInterval.y = Math.floor(remainder / SECONDS_IN_YEAR);
        remainder %= SECONDS_IN_YEAR;
        newInterval.m = Math.floor(remainder / SECONDS_IN_MONTH);
        remainder %= SECONDS_IN_MONTH;
        newInterval.d = Math.floor(remainder / SECONDS_IN_DAY);
        remainder %= SECONDS_IN_DAY;
        newInterval.h = Math.floor(remainder / SECONDS_IN_HOUR);
        remainder %= SECONDS_IN_HOUR;
        newInterval.i = Math.floor(remainder / SECONDS_IN_MINUTE);
        remainder %= SECONDS_IN_MINUTE;
        newInterval.s = Math.floor(remainder);

        return newInterval;
    }

    isIntervalEmpty(interval) {
        const {y = 0, m = 0, d = 0, h = 0, i = 0, s = 0} = interval;
        return y === 0 && m === 0 && d === 0 && h === 0 && i === 0 && s === 0;
    }

}
