# Dockerfile
FROM php:8.3-fpm

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl opcache intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Конфиги PHP
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www

# Зависимости (ключевой момент!)
# Копируем сначала манифесты — для кэша слоёв
COPY src/composer.json src/composer.lock ./

# Флаг --no-dev контролируется через ARG
ARG INSTALL_DEV=false
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --no-interaction --no-progress --no-scripts; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts; \
    fi

# Исходный код
COPY src/ .

# Права доступа
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]