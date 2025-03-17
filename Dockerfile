FROM php:8.1-cli

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y unzip git libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
