# Use PHP 8.2
FROM php:8.2-fpm-alpine AS template

ARG user=radian
ARG uid=1000

# Install minimal runtime packages and build, then clean build deps in one layer
RUN set -eux; \
    apk add --no-cache \
        ca-certificates \
        oniguruma \
        libzip \
        libpng \
        libjpeg-turbo \
        libpq \
        imagemagick; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        autoconf \
        build-base \
        linux-headers \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        libpq-dev \
        oniguruma-dev \
        imagemagick-dev \
        git \
        unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_pgsql \
        sockets \
        zip; \
    pecl install imagick; \
    docker-php-ext-enable imagick sockets; \
    apk del --no-network .build-deps; \
    rm -rf /tmp/* /var/cache/apk/* /usr/src/php* /usr/local/src/*

# Set the working directory
WORKDIR /var/www/mgn

# Copy composer (temporarily used during build)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Leverage layer caching: install dependencies first
COPY composer.json composer.lock app/helpers.php /var/www/mgn/

# Install composer dependencies (no dev) and optimize autoloader without running scripts
ARG COMPOSER_NO_DEV=1
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN --mount=type=cache,target=/tmp/cache \
    if [ "$COMPOSER_NO_DEV" = "1" ]; then \
        composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts; \
    else \
        composer install --prefer-dist --optimize-autoloader --no-interaction --no-scripts; \
    fi && \
    composer dump-autoload --optimize --no-scripts && \
    rm -rf /root/.composer

# Copy the rest of the application source
COPY --chown=$user:$user . /var/www/mgn
RUN composer dump-autoload --optimize --no-scripts

# Ensure required Laravel runtime directories exist even if not copied
RUN mkdir -p /var/www/mgn/storage/framework/cache \
    /var/www/mgn/storage/framework/sessions \
    /var/www/mgn/storage/framework/views \
    /var/www/mgn/storage/logs \
    /var/www/mgn/bootstrap/cache

# Add user and set permission
RUN addgroup -S $user && adduser -S $user -G www-data && \
    mkdir -p /etc/supervisor /var/log/supervisor && \
    chown -R $user:www-data /usr/local/var/log /etc/supervisor /var/log/supervisor /var/www/mgn && \
    chmod -R 775 /var/www/mgn/storage /var/www/mgn/bootstrap/cache /usr/local/var/log /etc/supervisor /var/log/supervisor

# Copy custom PHP-FPM configuration
COPY deployment/config/fpm/custom-php-fpm.conf /usr/local/etc/php-fpm.d/
COPY deployment/config/php/php.ini /usr/local/etc/php/php.ini

# Copy entrypoint script and ensure it's executable
COPY ./deployment/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

FROM template AS api

USER $user

EXPOSE 9001

CMD ["sh", "-c", "/entrypoint.sh"]

# Worker Image
FROM template AS worker

RUN set -eux; \
    apk add --no-cache supervisor; \
    mkdir -p /var/log/supervisor /var/www/mgn/storage/logs

# Copy Supervisor configuration for worker
COPY deployment/config/supervisor /etc/supervisor/conf.d
COPY deployment/config/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

RUN echo "* * * * * www-data php /var/www/mgn/artisan schedule:run >> /var/log/cron.log 2>&1" > /var/spool/cron/crontabs/www-data && \
    chmod 0644 /var/spool/cron/crontabs/www-data

CMD ["sh", "-c", "/entrypoint.sh && supervisord -n -c /etc/supervisor/supervisord.conf"]