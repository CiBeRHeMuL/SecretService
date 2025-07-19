FROM php:8.4-alpine AS builder

ENV TIME_ZONE=Europe/Moscow

WORKDIR /app

RUN apk add --no-cache \
          git \
          wget \
          postgresql-libs \
          postgresql-dev \
          tzdata

RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
    && echo "Europe/Moscow" > /etc/timezone

RUN wget "http://browscap.org/stream?q=Lite_PHP_BrowsCapINI" -O /usr/local/etc/php/php_browscap.ini

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl opcache soap apcu pgsql pdo pdo_pgsql bcmath pcntl zip gd sockets

COPY ./docker/php/ /usr/local/etc/
RUN rm /usr/local/etc/php/conf.d/base.ini -f

ENTRYPOINT exec php /app/bin/console messenger:consume --all --time-limit=3600 -vv >> /app/var/log/messenger-$(date +%Y-%m-%d).log 2>&1
