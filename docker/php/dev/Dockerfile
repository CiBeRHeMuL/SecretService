FROM php:8.4-fpm AS builder

ENV TIME_ZONE Europe/Moscow

WORKDIR /app

RUN apt update \
    && apt install -y git wget libpq-dev tzdata npm \
    && rm -rf /var/lib/apt/lists/*

RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
    && echo "Europe/Moscow" > /etc/timezone

RUN wget "http://browscap.org/stream?q=Lite_PHP_BrowsCapINI" -O /usr/local/etc/php/php_browscap.ini

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl opcache soap apcu pgsql pdo pdo_pgsql bcmath pcntl zip gd sockets xdebug

COPY ./docker/php/etc/ /usr/local/etc/
COPY ./docker/php/dev/etc/ /usr/local/etc/
RUN rm /usr/local/etc/php/conf.d/base.ini -f

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

RUN npm install -g pnpm
