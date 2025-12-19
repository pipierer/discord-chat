# Base image
FROM php:8.2-apache

# Installer dépendances pour PostgreSQL et PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copier le code dans le conteneur
COPY . /var/www/html/

# Droits Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Activer mod_rewrite pour Apache si besoin
RUN a2enmod rewrite

# Exposer le port HTTP
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]
