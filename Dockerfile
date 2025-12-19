FROM php:8.2-apache

# Installer les dépendances pour pdo_pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copier le code
COPY . /var/www/html/

# Mettre les droits
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
