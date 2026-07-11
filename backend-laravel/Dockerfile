FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.3-fpm-alpine
RUN apk add --no-cache bash curl icu-dev libzip-dev postgresql-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pdo_mysql intl mbstring bcmath pcntl opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY docker/php/entrypoint.sh /usr/local/bin/warqna-entrypoint
RUN chmod +x /usr/local/bin/warqna-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
ENTRYPOINT ["warqna-entrypoint"]
CMD ["php-fpm"]
