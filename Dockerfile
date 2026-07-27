FROM php:8.2-apache

# Install required dependencies
RUN apt-get update && apt-get install -y \
    openssl \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules for Rewrite (.htaccess) and SSL (HTTPS)
RUN a2enmod rewrite ssl

# Generate a self-signed SSL certificate (Valid for 10 years)
RUN openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
    -keyout /etc/ssl/private/ssl-cert-snakeoil.key \
    -out /etc/ssl/certs/ssl-cert-snakeoil.pem \
    -subj "/C=PH/ST=Zamboanga Sibugay/L=Local/O=DocMarly/CN=localhost"

# Enable the default SSL site that comes with Apache
RUN a2ensite default-ssl

# Set the working directory
WORKDIR /var/www/html

# Expose HTTP and HTTPS ports
EXPOSE 80
EXPOSE 443
