FROM php:8.2-apache

# Install PDO MySQL extension (PDO SQLite is enabled by default in this image)
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html