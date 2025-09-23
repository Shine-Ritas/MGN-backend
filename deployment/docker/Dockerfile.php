# Use PHP 8.2
FROM php:8.2-fpm-alpine AS template

# Use www-data user for proper web server permissions
ENV APP_USER=www-data \
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

# www-data user already exists in PHP image, just ensure home directory
RUN set -eux; \
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

# Create log directories and set permissions for PHP-FPM
RUN set -eux; \
    mkdir -p /var/log /usr/local/var/log; \
    touch /var/log/fpm-php.www.log /var/log/php_errors.log; \
    touch /usr/local/var/log/php-fpm.log; \
    chown -R $APP_USER:$APP_USER /var/log /usr/local/var; \
    chmod -R 775 /var/log /usr/local/var

USER $APP_USER

# Set proper umask for directory creation
RUN echo "umask 0002" >> /home/$APP_USER/.bashrc && \
    echo "umask 0002" >> /home/$APP_USER/.profile

# Install PHP and Node dependencies
RUN umask 0002 && composer install --optimize-autoloader  \
    && php artisan view:clear \
    && php artisan route:clear \
    && php artisan config:clear \
    && php artisan optimize

EXPOSE 9001

CMD sh -c 'umask 0002 && exec php-fpm -y /usr/local/etc/php-fpm.d/www.conf -R'

FROM template AS worker

RUN apk add --no-cache supervisor netcat-openbsd python3 py3-pip && \
    pip3 install --upgrade setuptools==80.0.0 supervisor --break-system-packages

# Create log directories and give radian ownership
RUN set -eux; \
    mkdir -p /var/log/supervisor /usr/local/var/log; \
    touch /var/log/supervisord.log /var/log/laravel-queue.log /var/log/wait-for-redis.log; \
    touch /usr/local/var/log/php-fpm.log; \
    chown -R $APP_USER:$APP_USER /var/log /usr/local/var; \
    chmod -R 775 /var/log /usr/local/var

COPY deployment/config/supervisor/supervisord.conf /etc/supervisord.conf

USER $APP_USER

# Set proper umask for directory creation  
RUN echo "umask 0002" >> /home/$APP_USER/.bashrc && \
    echo "umask 0002" >> /home/$APP_USER/.profile

RUN umask 0002 && composer install --optimize-autoloader \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

CMD sh -c 'umask 0002 && exec /usr/bin/supervisord -c /etc/supervisord.conf'
