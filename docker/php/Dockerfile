# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-bookworm AS base

ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip curl postgresql-client \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        gd \
        exif \
        pcntl \
        opcache \
        redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --gid ${GID} laravel \
    && useradd --uid ${UID} --gid ${GID} --create-home --shell /bin/bash laravel \
    && sed -i 's/^user = www-data/user = laravel/; s/^group = www-data/group = laravel/' /usr/local/etc/php-fpm.d/www.conf

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

FROM base AS dev

RUN install-php-extensions xdebug
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

USER laravel

CMD ["php-fpm"]
