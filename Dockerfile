FROM php:8.4-fpm-alpine

WORKDIR /nutrition-service

RUN apk update && apk add --no-cache \
    postgresql-dev \
    busybox \
    && docker-php-ext-install pdo_pgsql bcmath

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . /nutrition-service

RUN composer install --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /nutrition-service/storage /nutrition-service/bootstrap/cache \
    && chmod -R 775 /nutrition-service/storage /nutrition-service/bootstrap/cache

RUN mkdir -p /nutrition-service/storage/logs && \
    chown -R www-data:www-data /nutrition-service/storage && \
    chmod -R 775 /nutrition-service/storage

RUN php artisan config:cache \
   && php artisan route:cache

COPY crontab /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root \
    && crontab /etc/crontabs/root

COPY startapp.sh /usr/local/bin/startapp.sh
RUN chmod +x /usr/local/bin/startapp.sh
CMD ["startapp.sh"]

EXPOSE 9000
