FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www