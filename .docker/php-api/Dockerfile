# Use a base image that already has Apache and PHP
FROM php:8.2.27-apache

# Install GD library and its dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# (Optional) Install any additional PHP extensions you require
RUN apt-get update && apt-get install -y

RUN docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable xdebug mysqli \
    && docker-php-source delete \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable mod_headers and create a custom Apache config
RUN a2enmod headers
RUN a2enmod rewrite

# Create a custom Apache configuration file
#COPY .docker/php-api/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set the working directory inside the container
WORKDIR /var/www/html
