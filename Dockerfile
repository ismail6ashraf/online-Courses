FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000

CMD php artisan migrate --force && php artisan db:seed --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
