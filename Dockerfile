FROM php:8.3-cli-alpine

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        zip \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
