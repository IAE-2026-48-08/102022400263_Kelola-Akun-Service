FROM php:8.2-apache

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache document root ke Laravel /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Salin seluruh project ke container
COPY . /var/www/html

WORKDIR /var/www/html

# Install PHP dependencies via Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup .env dari .env.example jika .env belum ada, lalu generate APP_KEY
RUN cp -n .env.example .env || true
RUN php artisan key:generate --force

# Generate Swagger docs and clear Lighthouse cache on build
RUN php artisan l5-swagger:generate
RUN php artisan lighthouse:clear-cache

# [BYPASS] Paksa Apache berjalan sebagai root agar bebas menulis cache walau di-bind mount
ENV APACHE_RUN_USER=root
ENV APACHE_RUN_GROUP=root

EXPOSE 80