FROM php:latest

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get install -y libxml2-dev \
    && docker-php-ext-install xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

RUN composer require "ext-gd:*" --ignore-platform-reqs
RUN composer require phpoffice/phpspreadsheet

RUN composer install

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/public"]