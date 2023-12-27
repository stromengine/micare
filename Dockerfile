# Use the official PHP image as a base
FROM php:7.4-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Install the PostgreSQL extension
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql pgsql

COPY . .

# Expose port 80 for the Apache web server
EXPOSE 80

# Start the Apache web server when the container starts
CMD ["apache2-foreground"]