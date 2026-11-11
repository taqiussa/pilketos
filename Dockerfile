FROM node:24-alpine AS frontend_builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM php:8.4-apache AS runtime

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    libonig-dev \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=frontend_builder /app/public/build ./public/build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel-public.conf \
    && a2enconf laravel-public \
    && test -f /var/www/html/public/index.php \
    && test -f /var/www/html/public/build/manifest.json

COPY healthcheck.sh /usr/local/bin/healthcheck
RUN chmod 0755 /usr/local/bin/healthcheck

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD /usr/local/bin/healthcheck || exit 1
