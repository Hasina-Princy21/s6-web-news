FROM php:8.3-fpm-alpine

# Install PostgreSQL client and extension
RUN apk add --no-cache \
        libpq \
        postgresql-client \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql \
    && apk del .build-deps

WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

USER www-data

CMD ["php-fpm"]