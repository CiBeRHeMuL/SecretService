# Secret Service | Начало работы

Описание настройки, установки и запуска приложения, в докере и без докера

---

### Git

- Добавляем хуки для `pre-commit`

```shell
pip install pre-commit
pre-commit install
```

---

### Конфигурация

Убедитесь, что у вас есть файл `.env.local` в корне с указанными необходимыми переменными окружения.

```sh
cp .env .env.local
```

Настройте параметры под себя:

| Параметр                             | Описание                                              | Test                                                 | Dev                                                                                                   | Prod                                                                                                 | Примечания                                                                                                                                                                                                                           |
|--------------------------------------|-------------------------------------------------------|------------------------------------------------------|-------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| DATABASE_URL                         | DSN подключения к базе данных PostgreSQL              | YOUR_DATABASE_DSN                                    | YOUR_DATABASE_DSN                                                                                     | YOUR_DATABASE_DSN                                                                                    | Формат описан в https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url                                                                                                |
| APP_ENV                              | Окружение приложения                                  | test                                                 | dev                                                                                                   | prod                                                                                                 | Окружение приложения (dev, test, prod)                                                                                                                                                                                               |
| APP_HOST                             | Базовый URL приложения                                | http://secret.loc                                    | http://secret.loc                                                                                     | PRODUCTION_HOST                                                                                      | Базовый URL, по которому доступно приложение                                                                                                                                                                                         |
| APP_SECRET                           | Секретный ключ приложения                             | openssl rand -hex 16                                 | openssl rand -hex 16                                                                                  | openssl rand -hex 16                                                                                 | Секретный ключ для безопасности приложения                                                                                                                                                                                           |
| NGINX_EXTERNAL_PORT                  | Внешний порт для Nginx                                | 80                                                   | 80                                                                                                    | 80                                                                                                   | Порт, на котором будет доступен веб-сервер                                                                                                                                                                                           |
| PG_EXTERNAL_PORT                     | Внешний порт PostgreSQL                               | 5432                                                 | 5432                                                                                                  | 5432                                                                                                 | Порт для подключения к PostgreSQL                                                                                                                                                                                                    |
| PG_USER                              | Пользователь PostgreSQL                               | POSTGRES_USER                                        | POSTGRES_USER                                                                                         | POSTGRES_USER                                                                                        | Имя пользователя для подключения к PostgreSQL                                                                                                                                                                                        |
| PG_PASSWORD                          | Пароль PostgreSQL                                     | POSTGRES_PASSWORD                                    | POSTGRES_PASSWORD                                                                                     | POSTGRES_PASSWORD                                                                                    | Пароль для подключения к PostgreSQL                                                                                                                                                                                                  |
| PG_DBNAME                            | Имя базы данных PostgreSQL                            | POSTGRES_DBNAME                                      | POSTGRES_DBNAME                                                                                       | POSTGRES_DBNAME                                                                                      | Название базы данных PostgreSQL                                                                                                                                                                                                      |
| TELEGRAM_CONFIG_ERROR_BOT_KEY        | Ключ бота Telegram для отправки ошибок                | -                                                    | -                                                                                                     | ERROR_CHAT_BOT_KEY                                                                                   | API ключ Telegram-бота для отправки сообщений об ошибках                                                                                                                                                                             |
| TELEGRAM_CONFIG_ERROR_CHAT_ID        | ID чата для отправки ошибок                           | -                                                    | -                                                                                                     | ERROR_CHAT_CHAT_ID                                                                                   | Идентификатор чата для отправки сообщений об ошибках                                                                                                                                                                                 |
| TELEGRAM_CONFIG_ERROR_TOPIC          | ID топика в чате ошибок                               | -                                                    | -                                                                                                     | ERROR_CHAT_TOPIC_ID                                                                                  | Идентификатор темы в чате для группировки сообщений об ошибках                                                                                                                                                                       |
| TRUSTED_PROXIES                      | Доверенные прокси                                     | 127.0.0.1, REMOTE_ADDR                               | 127.0.0.1, REMOTE_ADDR                                                                                | 127.0.0.1, REMOTE_ADDR                                                                               | Список доверенных прокси-серверов для корректной обработки запросов                                                                                                                                                                  |
| TRUSTED_HEADERS                      | Доверенные заголовки                                  | x-forwarded-for, x-forwarded-proto, x-forwarded-host | x-forwarded-for, x-forwarded-proto, x-forwarded-host                                                  | x-forwarded-for, x-forwarded-proto                                                                   | Список доверенных HTTP-заголовков для обработки запросов через прокси                                                                                                                                                                |
| MESSENGER_TRANSPORT_DSN              | DSN для транспорта сообщений                          | sync://                                              | doctrine://default?auto_setup=1&queue_name=default &check_delayed_interval=1000&get_notify_timeout=10 | doctrine://default?auto_setup=1&queue_name=default&check_delayed_interval=1000&get_notify_timeout=10 | Строка подключения для очереди обработки сообщений                                                                                                                                                                                   |
| MESSENGER_TRANSPORT_LOG_DSN          | DSN для транспорта логов                              | sync://                                              | doctrine://default?queue_name=log&auto_setup=1&check_delayed_interval=1000&get_notify_timeout=10      | doctrine://default?queue_name=log&auto_setup=1&check_delayed_interval=1000&get_notify_timeout=10     | Строка подключения для очереди обработки сообщений логов                                                                                                                                                                             |
| MESSAGE_TEXT_ENCRYPTOR_CIPHER_METHOD | Метод шифрования OpenSSL для текста сообщения         | AES-256-CBC                                          | AES-256-CBC                                                                                           | AES-256-CBC                                                                                          | Метод шифрования, используемый для защиты данных. Выберите метод с помощью функции openssl_get_cipher_methods()                                                                                                                      |
| MESSAGE_TEXT_ENCRYPTOR_PASSPHRASE    | Ключевая фраза для шифрования текста сообщения        | Строка длиной 32 байта                               | Строка длиной 32 байта                                                                                | Строка длиной 32 байта                                                                               | Ключевая фраза для шифрования данных. Необходимую длину можно получить с помощью функции openssl_cipher_key_length()                                                                                                                 |
| MESSAGE_TEXT_ENCRYPTOR_IV            | Вектор инициализации для шифрования текста сообщения  | Строка длиной 16 байт                                | Строка длиной 16 байт                                                                                 | Строка длиной 16 байт                                                                                | Вектор инициализации для шифрования. Необходимую длину можно получить с помощью функции openssl_cipher_iv_length()                                                                                                                   |
| MESSAGE_ID_ENCRYPTOR_NONCE           | Кодовая фраза для шифрования идентификатора сообщения | Строка длиной 24 байта                               | Строка длиной 24 байта                                                                                | Строка длиной 24 байта                                                                               | Кодовая фраза для шифрования данных. Необходимую длину можно получить с помощью констант модуля sodium                                                                                                                               |
| MESSAGE_ID_ENCRYPTOR_KEY             | Ключ для шифрования идентификатора сообщения          | Строка длиной 32 байта                               | Строка длиной 32 байта                                                                                | Строка длиной 32 байта                                                                               | Ключ для шифрования. Необходимую длину можно получить с помощью констант модуля sodium                                                                                                                                               |
| MESSAGE_LIFETIME                     | Время жизни сообщения                                 | 60                                                   | 60                                                                                                    | 86400                                                                                                | Время жизни сообщения, если оно не было прочитано, можно указать количество секунд целым числом или числом с плавающей запятой, или строкой, валидной для передачи в конструктор DateInterval или DateInterval::createFromDateString |
| LOCK_DSN                             | DSN для блокировок                                    | flock                                                | flock                                                                                                 | flock                                                                                                | Строка подключения для блокировок                                                                                                                                                                                                    |
| RATE_LIMIT                           | Лимит запросов для скользящего окна                   | 20                                                   | 20                                                                                                    | 20                                                                                                   | Количество запросов, которые может сделать пользователь в рамках скользящего окна до получения 429 ошибки                                                                                                                            |
| RATE_INTERVAL                        | Размер скользящего окна                               | 10 seconds                                           | 10 seconds                                                                                            | 10 seconds                                                                                           | Размер скользящего окна для ограничителя запросов                                                                                                                                                                                    |

---

Добавляем локально хосты в hosts файл

```
127.0.0.1 secret.loc
```

---

### Запуск без Docker

#### Требования:

- Nginx
- PHP 8.4
- Make
- Composer
- PostgreSQL 17

#### Установка:

Выполняем основные команды необходимые для первичной настройки проекта.

- Устанавливаем composer зависимости

```sh
php composer install
```

- Накатываем миграции

```sh
make migrate
```

### Запуск с Docker

#### Требования:

- [Docker](https://www.docker.com/)
- Make

#### Установка:

**1.** Разворачиваем docker

```sh
make docker-install
```

**2.** Так как вся работа теперь связана с PHP контейнером, удобно будет добавить в систему алиас, для удобства работы с докером.

```sh
alias dphp='docker exec -it php-container'
```

Теперь вызов всех команд в Symfony будет выглядеть так:

```sh
dphp bin/console
```

или через make:

```sh
make dphp cmd="bin/console"
```

**3.** Далее можем выполнить основные команды необходимые для первичной настройки проекта.

- Устанавливаем composer зависимости

```sh
dphp composer install
```

- Накатываем миграции

```sh
make docker-migrate
```

#### Запуск:

```
    http://secret.loc
```
