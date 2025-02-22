# Use a base image that already has Apache and PHP
FROM php:8.2.27-apache

# IF RUNNING STATIC
# Copy your application code into the container
COPY ./api/ /var/www/html/

# (Optional) Set file permissions if needed (important for PHP uploads, etc.)
# RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# (Optional) Install any additional PHP extensions you require
RUN apt-get update && apt-get install -y

RUN docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable xdebug mysqli \
    && docker-php-source delete

# (Optional) Customize the Apache configuration
# COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
# RUN a2enmod rewrite && a2ensite 000-default.conf

# (Optional) Expose port 80 (default for HTTP) - usually not necessary with Docker's port mapping
# EXPOSE 80

# (Optional) Set the working directory inside the container
WORKDIR /var/www/html

# The CMD instruction is already defined in the php:*-apache base image
# It starts Apache, so you usually don't need to override it.
# If you *did* need to override it, it would look something like this:
# CMD ["apache2-foreground"]


