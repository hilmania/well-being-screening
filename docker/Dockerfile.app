# Dockerfile.app

FROM php:8.3-cli

# Install dependencies
RUN apt-get update && apt-get install -y zip libzip-dev libonig-dev gnupg

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip mbstring

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
  && apt-get install -y nodejs

# Cleanup
RUN rm -rf /var/lib/apt/lists/*
RUN apt-get remove -y gnupg && apt-get clean

WORKDIR /var/www/html