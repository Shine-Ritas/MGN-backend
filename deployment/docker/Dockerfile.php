# Use PHP 8.2
FROM php:8.2-fpm-alpine AS template

ENV APP_USER=www-data \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/mgn

# ---- Install PHP extensions ----
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

# ---- Config ----
COPY deployment/config/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY deployment/config/fpm/custom-php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Composer home
RUN set -eux; \
    mkdir -p /home/"$APP_USER"/.composer; \
    chown -R "$APP_USER":"$APP_USER" /home/"$APP_USER"

# Copy app code
COPY --chown=$APP_USER:$APP_USER . /var/www/mgn

# Prepare Laravel dirs
RUN set -eux; \
    mkdir -p /var/www/mgn/public/build; \
    mkdir -p \
        /var/www/mgn/storage/framework/cache \
        /var/www/mgn/storage/framework/sessions \
        /var/www/mgn/storage/framework/testing \
        /var/www/mgn/storage/framework/views \
        /var/www/mgn/storage/logs \
        /var/www/mgn/bootstrap/cache; \
    chown -R $APP_USER:$APP_USER /var/www/mgn; \
    chmod -R 755 /var/www/mgn/bootstrap/cache /var/www/mgn/public/build; \
    chmod -R 775 /var/www/mgn/storage

# ------------------ API IMAGE ------------------
FROM template AS api

# Logs
RUN set -eux; \
    mkdir -p /var/log /usr/local/var/log; \
    touch /var/log/fpm-php.www.log /var/log/php_errors.log; \
    touch /usr/local/var/log/php-fpm.log; \
    chown -R $APP_USER:$APP_USER /var/log /usr/local/var; \
    chmod -R 775 /var/log /usr/local/var; \
    chmod -R g+rwX /var/www/mgn/storage /var/www/mgn/bootstrap/cache


# Copy entrypoint
COPY deployment/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER $APP_USER


# Install dependencies
RUN umask 0002 && composer install --optimize-autoloader \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan optimize

EXPOSE 9001
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.d/www.conf", "-R"]

# ------------------ WORKER IMAGE ------------------
FROM template AS worker

RUN apk add --no-cache supervisor netcat-openbsd python3 py3-pip && \
    pip3 install --upgrade setuptools==80.0.0 supervisor --break-system-packages

RUN set -eux; \
    mkdir -p /var/log/supervisor /usr/local/var/log; \
    touch /var/log/supervisord.log /var/log/laravel-queue.log /var/log/wait-for-redis.log; \
    touch /usr/local/var/log/php-fpm.log; \
    chown -R $APP_USER:$APP_USER /var/log /usr/local/var; \
    chmod -R 775 /var/log /usr/local/var; \
    chmod -R g+rwX /var/www/mgn/storage /var/www/mgn/bootstrap/cache

COPY deployment/config/supervisor/supervisord.conf /etc/supervisord.conf

USER $APP_USER

RUN umask 0002 && composer install --optimize-autoloader \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
