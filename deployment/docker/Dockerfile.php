# Use PHP 8.2
FROM php:8.2-fpm-alpine AS template

ARG user=radian
ARG uid=1099
ARG gid=1099

# Environment so user is also available at runtime
ENV APP_USER=$user \
    APP_UID=$uid \
    APP_GID=$gid \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/mgn

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

COPY deployment/config/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY deployment/config/fpm/custom-php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy composer (temporarily used during build)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Create group and user dynamically
RUN set -eux; \
    addgroup -g "$APP_GID" "$APP_USER"; \
    adduser -u "$APP_UID" -G "$APP_USER" -D -s /bin/bash "$APP_USER"; \
    mkdir -p /home/"$APP_USER"/.composer; \
    chown -R "$APP_USER":"$APP_USER" /home/"$APP_USER"

# Copy app code
COPY --chown=$APP_USER:$APP_USER . /var/www/mgn

RUN set -eux; \
    mkdir -p /var/www/mgn/public/build && \
    # Ensure Laravel cache/compiled directories exist before running composer/artisan
    mkdir -p \
        /var/www/mgn/storage/framework/cache \
        /var/www/mgn/storage/framework/sessions \
        /var/www/mgn/storage/framework/testing \
        /var/www/mgn/storage/framework/views \
        /var/www/mgn/storage/logs \
        /var/www/mgn/bootstrap/cache && \
    \
    chown -R $APP_USER:$APP_USER /var/www/mgn; \
    \
    chmod -R 755 /var/www/mgn/bootstrap/cache /var/www/mgn/public/build; \
    chmod -R 775 /var/www/mgn/storage

FROM template AS api

USER $APP_USER
# Install PHP and Node dependencies
RUN composer install --optimize-autoloader  \
    && php artisan view:clear \
    && php artisan route:clear \
    && php artisan config:clear \
    && php artisan optimize

EXPOSE 9001

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.d/www.conf", "-R"]

FROM template AS worker

# Install supervisor, netcat, and Python/pip
RUN apk add supervisor netcat-openbsd python3 py3-pip && \
    pip3 install --upgrade setuptools==80.0.0 supervisor --break-system-packages

# Create log directories and set permissions
RUN set -eux; \
    mkdir -p /var/log/supervisor /usr/local/var/log; \
    touch /var/log/{supervisord.log,laravel-queue.log,wait-for-redis.log,fpm-php.www.log,php_errors.log}; \
    touch /usr/local/var/log/php-fpm.log; \
    chown -R $APP_USER:$APP_USER /var/log /usr/local/var/log; \
    chmod -R 755 /var/log /usr/local/var/log; \
    chmod -R 775 /var/log/supervisor

# Copy supervisor configuration
COPY deployment/config/supervisor/supervisord.conf /etc/supervisord.conf

# Switch to non-root user for install
USER $APP_USER

# Install PHP dependencies
RUN composer install --optimize-autoloader \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Expose supervisor port
EXPOSE 9001

# Start supervisor as $APP_USER user
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
