FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update --fix-missing && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    libicu-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN mkdir -p /var/www/storage && chmod -R 777 /var/www/storage

EXPOSE 8080

CMD ["php-fpm"]
