# 開発も本番も同じ PHP 8.5 イメージを使う。
# 開発用 compose は target: dev（ソースはマウント）。
# 本番用 compose は target: prod（コードをイメージに入れる）。

ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-apache AS base

# mbstring と OPcache は公式イメージに入っている。重ねて入れるとビルドが失敗する。
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ghostscript \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath exif \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/denshichobo.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]

FROM base AS dev

FROM base AS prod

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction --no-scripts \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
