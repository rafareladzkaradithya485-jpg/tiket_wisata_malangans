FROM php:8.2-apache
# ... (kode Dockerfile Anda yang sudah ada di atas) ...

# 🛑 Tambahkan baris ini untuk mematikan modul MPM yang bentrok
RUN a2dismod mpm_event || true

# Pastikan port diarahkan ke Apache bawaan
EXPOSE 80
RUN apt-get update && apt-get install -y \
    unzip \
    zlib1g-dev \
 && docker-php-ext-install mysqli pdo pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

ENV PORT=8080

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 8080
CMD ["bash", "-lc", "export APACHE_RUN_PORT=${PORT:-8080} && exec apache2-foreground"]
