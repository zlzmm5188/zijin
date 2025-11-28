# PHP + Nginx Docker Image for Railway Deployment
FROM php:8.2-fpm-alpine

# Install required extensions and nginx
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        curl \
        gd \
        zip \
        opcache \
    && rm -rf /var/cache/apk/*

# Create application directory
WORKDIR /app

# Copy application source
COPY . .

# Install Composer dependencies for PHP API
WORKDIR /app/providence-admin
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

WORKDIR /app

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy PHP-FPM configuration
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisord configuration
COPY supervisord.conf /etc/supervisord.conf

# Create required directories
RUN mkdir -p /run/nginx /var/log/nginx /app/providence-admin/logs \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Default Railway port
ENV PORT=8080
EXPOSE 8080

# Start services via supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]