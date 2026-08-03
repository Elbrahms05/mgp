# Image de développement du back-office Laravel PACIPA-MGP (PHP-FPM 8.4).
FROM php:8.4-fpm-alpine

# Dépendances système pour les extensions PHP requises par le projet.
RUN apk add --no-cache \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libsodium-dev \
        postgresql-dev \
        oniguruma-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        intl \
        sodium \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Le code est monté en volume en dev (voir docker-compose.yml).
EXPOSE 9000
CMD ["php-fpm"]
