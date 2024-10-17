FROM php:8.3-apache

# Install PostgreSQL extension for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set the working directory to /var/www/html/src
WORKDIR /var/www/html/src

# Update Apache's DocumentRoot to /var/www/html/src
RUN sed -i 's|/var/www/html|/var/www/html/src|g' /etc/apache2/sites-available/000-default.conf

# Optionally, you can add any additional PHP configuration or dependencies

# Salin file php.ini dari direktori lokal ke direktori yang benar di dalam container
COPY ./php/php.ini /usr/local/etc/php/php.ini

