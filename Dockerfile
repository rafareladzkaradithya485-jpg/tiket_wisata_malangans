FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev default-mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

CMD ["/usr/local/bin/entrypoint.sh"]