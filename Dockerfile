FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    zlib1g-dev \
 && docker-php-ext-install mysqli pdo pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

ENV APACHE_RUN_PORT=8080
ENV PORT=8080

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 8080
CMD ["apache2-foreground"]
