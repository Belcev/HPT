FROM php:8.4.18-fpm

RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        sudo \
        bash \
        zip \
        unzip \
        libzip-dev

RUN docker-php-ext-install  \
        zip  \
        pdo  \
        pdo_mysql

ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
